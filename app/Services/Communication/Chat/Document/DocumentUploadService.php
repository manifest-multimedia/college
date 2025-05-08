<?php

namespace App\Services\Communication\Chat\Document;

use App\Events\Communication\DocumentUploadedEvent;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentUploadService
{
    /**
     * Upload a document to a chat session.
     *
     * @param string $sessionId
     * @param UploadedFile $file
     * @return array
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
            
            if (!$path) {
                return [
                    'success' => false,
                    'message' => 'Failed to store document'
                ];
            }
            
            // Create chat message for document
            $message = ChatMessage::create([
                'chat_session_id' => $session->id,
                'user_id' => Auth::id(),
                'type' => 'user',
                'is_document' => true,
                'message' => 'Document uploaded: ' . $file->getClientOriginalName(),
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
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
            ];
            
            // Broadcast document uploaded event
            broadcast(new DocumentUploadedEvent($sessionId, $messageData))->toOthers();
            
            return [
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => $messageData
            ];
        } catch (\Exception $e) {
            Log::error('Failed to upload document', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
                'file' => $file->getClientOriginalName(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate the uploaded file.
     *
     * @param UploadedFile $file
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
            'text/csv'
        ];
        
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            throw new \Exception('File type not allowed');
        }
    }
    
    /**
     * Generate a unique filename for the uploaded file.
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function generateUniqueFileName(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $uniqueId = Str::uuid()->toString();
        
        return "{$baseName}-{$uniqueId}.{$extension}";
    }
}