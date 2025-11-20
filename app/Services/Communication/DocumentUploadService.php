<?php

namespace App\Services\Communication;

use App\Models\Communication\ChatMessage;
use App\Models\Communication\ChatSession;
use App\Services\Communication\Chat\OpenAI\OpenAIFilesService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentUploadService
{
    /**
     * OpenAI Files Service
     */
    protected $openaiFilesService;

    /**
     * Constructor
     */
    public function __construct(OpenAIFilesService $openaiFilesService)
    {
        $this->openaiFilesService = $openaiFilesService;
    }

    /**
     * Upload document for a chat session
     *
     * @param  string  $sessionId  The chat session ID
     * @param  UploadedFile  $document  The document file
     */
    public function uploadDocument(string $sessionId, UploadedFile $document): array
    {
        try {
            // Get the chat session
            $chatSession = ChatSession::findOrFail($sessionId);

            // Generate a unique filename
            $fileName = time().'_'.$document->getClientOriginalName();

            // Define storage path
            $path = "chat/{$sessionId}/documents";

            // Store the document
            $storedPath = Storage::disk('public')->putFileAs($path, $document, $fileName);

            if (! $storedPath) {
                return [
                    'success' => false,
                    'message' => 'Failed to store document',
                ];
            }

            // Get full path for the file
            $fullPath = Storage::disk('public')->path($storedPath);

            // Upload to OpenAI
            $openaiUploadResult = $this->openaiFilesService->uploadFile($fullPath);
            $openaiFileId = $openaiUploadResult['file_id'] ?? null;

            // Create a document message
            $message = new ChatMessage([
                'sender_id' => auth()->id(),
                'content' => "Document: {$document->getClientOriginalName()}",
                'chat_session_id' => $sessionId,
                'is_document' => true,
                'document_path' => $storedPath,
                'openai_file_id' => $openaiFileId,
            ]);

            $message->save();

            return [
                'success' => true,
                'file_path' => $storedPath,
                'message_id' => $message->id,
                'openai_file_id' => $openaiFileId,
            ];
        } catch (\Exception $e) {
            Log::error('Document upload failed', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
                'file_name' => $document->getClientOriginalName(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to upload document: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Delete a document
     *
     * @param  string  $documentPath  The path to the document
     * @param  string|null  $openaiFileId  The OpenAI file ID if exists
     */
    public function deleteDocument(string $documentPath, ?string $openaiFileId = null): array
    {
        try {
            // Delete from storage
            if (Storage::disk('public')->exists($documentPath)) {
                Storage::disk('public')->delete($documentPath);
            }

            // Delete from OpenAI if we have an ID
            if ($openaiFileId) {
                $this->openaiFilesService->deleteFile($openaiFileId);
            }

            return [
                'success' => true,
                'message' => 'Document deleted successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Document deletion failed', [
                'error' => $e->getMessage(),
                'document_path' => $documentPath,
                'openai_file_id' => $openaiFileId,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to delete document: '.$e->getMessage(),
            ];
        }
    }
}
