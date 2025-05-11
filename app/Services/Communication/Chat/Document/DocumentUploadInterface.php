<?php

namespace App\Services\Communication\Chat\Document;

use Illuminate\Http\UploadedFile;

interface DocumentUploadInterface
{
    /**
     * Upload a document to storage and register with AI service if needed
     * 
     * @param string $sessionId
     * @param UploadedFile $file
     * @param array $options
     * @return array
     */
    public function uploadDocument(string $sessionId, UploadedFile $file, array $options = []);

    /**
     * Generate a download URL for a document
     * 
     * @param string $path
     * @return array
     */
    public function getDownloadUrl(string $path);

    /**
     * Delete a document
     * 
     * @param string $path
     * @param string|null $aiFileId
     * @return array
     */
    public function deleteDocument(string $path, ?string $aiFileId = null);
}