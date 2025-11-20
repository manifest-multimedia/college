<?php

namespace App\Services\Communication\Chat\Document;

use App\Events\Communication\DocumentUploadedEvent;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\Communication\Chat\OpenAI\OpenAIFilesService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentUploadService
{
    /**
     * OpenAI Files Service instance
     */
    protected OpenAIFilesService $openAIFilesService;

    /**
     * Constructor
     */
    public function __construct(OpenAIFilesService $openAIFilesService)
    {
        $this->openAIFilesService = $openAIFilesService;
    }

    /**
     * Upload a document to a chat session.
     */
    public function uploadDocument(string $sessionId, UploadedFile $file): array
    {
        try {
            // Get chat session
            $session = ChatSession::where('session_id', $sessionId)
                ->where('status', '!=', 'deleted')
                ->firstOrFail();

            // Validate file
            $this->validateFile($file);

            // Generate a unique filename
            $fileName = $this->generateUniqueFileName($file);

            // Store the file using the public disk instead of s3
            $path = $file->storeAs('chat_documents', $fileName, 'public');

            if (! $path) {
                return [
                    'success' => false,
                    'message' => 'Failed to store document',
                ];
            }

            // Upload file to OpenAI Files API for advanced processing
            $fullPath = Storage::disk('public')->path($path);
            $openAIUpload = $this->openAIFilesService->uploadFile($fullPath);
            $openAIFileId = null;

            if ($openAIUpload['success']) {
                $openAIFileId = $openAIUpload['file_id'];
                Log::info('Document uploaded to OpenAI Files API', [
                    'file_name' => $file->getClientOriginalName(),
                    'openai_file_id' => $openAIFileId,
                    'session_id' => $sessionId,
                ]);
            } else {
                // Log the error but continue with the flow
                Log::warning('Failed to upload document to OpenAI Files API', [
                    'file_name' => $file->getClientOriginalName(),
                    'error' => $openAIUpload['message'],
                    'session_id' => $sessionId,
                ]);
            }

            // Create chat message for document
            $message = ChatMessage::create([
                'chat_session_id' => $session->id,
                'user_id' => Auth::id(),
                'type' => 'user',
                'is_document' => true,
                'message' => 'Document uploaded: '.$file->getClientOriginalName(),
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'metadata' => [
                    'openai_file_id' => $openAIFileId,
                ],
            ]);

            // Update session last activity
            $session->update([
                'last_activity_at' => now(),
            ]);

            // Format message for response
            $messageData = [
                'id' => $message->id,
                'type' => $message->type,
                'message' => $message->message,
                'is_document' => true,
                'file_path' => $message->file_path,
                'file_name' => $message->file_name,
                'mime_type' => $message->mime_type,
                'file_size' => $message->file_size,
                'timestamp' => $message->created_at,
                'openai_file_id' => $openAIFileId,
            ];

            // Broadcast document uploaded event
            broadcast(new DocumentUploadedEvent($sessionId, $messageData))->toOthers();

            return [
                'success' => true,
                'message' => 'Document uploaded successfully',
                'file_path' => $path,
                'openai_file_id' => $openAIFileId,
                'data' => $messageData,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to upload document', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
                'file' => $file->getClientOriginalName(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to upload document: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Validate the uploaded file.
     *
     * @throws \Exception
     */
    protected function validateFile(UploadedFile $file): void
    {
        // Check file size (max 10MB)
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file->getSize() > $maxSize) {
            throw new \Exception('File size exceeds the maximum allowed size (10MB)');
        }

        // Check file type
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'text/csv',
        ];

        if (! in_array($file->getMimeType(), $allowedTypes)) {
            throw new \Exception('File type not allowed');
        }
    }

    /**
     * Generate a unique filename for the uploaded file.
     */
    protected function generateUniqueFileName(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $uniqueId = Str::uuid()->toString();

        return "{$baseName}-{$uniqueId}.{$extension}";
    }
}
