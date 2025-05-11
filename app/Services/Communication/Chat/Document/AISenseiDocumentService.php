<?php

namespace App\Services\Communication\Chat\Document;

use App\Events\Communication\DocumentUploadedEvent;
use App\Models\ChatSession;
use App\Services\Communication\Chat\OpenAI\OpenAIAssistantsService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AISenseiDocumentService implements DocumentUploadInterface
{
    /**
     * OpenAI Assistants API Service
     */
    protected OpenAIAssistantsService $assistantsService;

    /**
     * Constructor
     */
    public function __construct(OpenAIAssistantsService $assistantsService)
    {
        $this->assistantsService = $assistantsService;
    }

    /**
     * Upload a document to storage and register with OpenAI if needed
     * 
     * @param string $sessionId
     * @param UploadedFile $file
     * @param array $options
     * @return array
     */
    public function uploadDocument(string $sessionId, UploadedFile $file, array $options = [])
    {
        try {
            // Check if session exists
            $session = ChatSession::where('session_id', $sessionId)->first();
            
            if (!$session) {
                return [
                    'success' => false,
                    'message' => 'Chat session not found',
                ];
            }
            
            // Store the file locally first
            $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $sanitizedFileName = Str::slug($fileName) . '-' . time() . '.' . $extension;
            
            $path = $file->storeAs(
                'chat_documents/' . $sessionId,
                $sanitizedFileName,
                'public'
            );
            
            if (!$path) {
                return [
                    'success' => false,
                    'message' => 'Failed to save document',
                ];
            }
            
            $publicPath = Storage::url($path);
            $fullPath = storage_path('app/public/' . $path);
            
            // Upload to OpenAI
            $uploadResult = $this->assistantsService->uploadFile($fullPath, 'assistants');
            
            if (!$uploadResult['success']) {
                Log::warning('Failed to upload document to OpenAI, but saved locally', [
                    'session_id' => $sessionId,
                    'file_path' => $path,
                    'error' => $uploadResult['message'] ?? 'Unknown error',
                ]);
                
                // We continue even if OpenAI upload fails, since we have the local file
            }
            
            // Save metadata about the file
            $fileMetadata = [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'stored_path' => $path,
                'public_path' => $publicPath,
                'openai_file_id' => $uploadResult['success'] ? $uploadResult['data']['id'] : null,
                'openai_upload_status' => $uploadResult['success'] ? 'success' : 'failed',
                'uploaded_at' => now()->toIso8601String(),
            ];
            
            // Send event notification
            $uploadNotification = [
                'session_id' => $sessionId,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'upload_status' => $uploadResult['success'] ? 'success' : 'partial',
                'uploaded_at' => now(),
            ];
            
            broadcast(new DocumentUploadedEvent($sessionId, $uploadNotification))->toOthers();
            
            // Return success
            return [
                'success' => true,
                'message' => 'Document uploaded successfully',
                'file_path' => $path,
                'public_url' => $publicPath,
                'file_name' => $file->getClientOriginalName(),
                'openai_file_id' => $uploadResult['success'] ? $uploadResult['data']['id'] : null,
                'metadata' => $fileMetadata,
            ];
        } catch (\Exception $e) {
            Log::error('Document upload failed', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Document upload failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate a download URL for a document
     * 
     * @param string $path
     * @return array
     */
    public function getDownloadUrl(string $path)
    {
        try {
            if (!Storage::disk('public')->exists($path)) {
                return [
                    'success' => false,
                    'message' => 'Document not found',
                ];
            }
            
            $url = Storage::disk('public')->url($path);
            
            return [
                'success' => true,
                'url' => $url,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get document download URL', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to get document download URL',
            ];
        }
    }

    /**
     * Delete a document
     * 
     * @param string $path
     * @param string|null $openaiFileId
     * @return array
     */
    public function deleteDocument(string $path, ?string $openaiFileId = null)
    {
        try {
            $deleted = Storage::disk('public')->delete($path);
            
            if (!$deleted) {
                return [
                    'success' => false,
                    'message' => 'Document not found or could not be deleted',
                ];
            }
            
            // Also delete from OpenAI if we have a file ID
            if ($openaiFileId) {
                $deleteResult = $this->assistantsService->deleteFile($openaiFileId);
                
                if (!$deleteResult['success']) {
                    Log::warning('Failed to delete document from OpenAI', [
                        'openai_file_id' => $openaiFileId,
                        'error' => $deleteResult['message'] ?? 'Unknown error',
                    ]);
                }
            }
            
            return [
                'success' => true,
                'message' => 'Document deleted successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Document deletion failed', [
                'path' => $path,
                'openai_file_id' => $openaiFileId,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Document deletion failed: ' . $e->getMessage(),
            ];
        }
    }
}