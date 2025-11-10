<?php

namespace App\Services\Communication\Chat\OpenAI;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OpenAIFilesService
{
    /**
     * OpenAI API URLs (non-sensitive configuration)
     */
    protected $baseUrl;
    
    /**
     * Local storage configuration
     */
    protected $diskName;
    protected $storagePath;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->baseUrl = Config::get('services.openai.base_url', 'https://api.openai.com/v1');
        
        $this->diskName = Config::get('services.openai.storage_disk', 'local');
        $this->storagePath = Config::get('services.openai.storage_path', 'openai-files');
    }
    
    /**
     * Get the API key dynamically from config
     * This prevents caching of sensitive credentials in singleton instances
     * 
     * @return string|null
     */
    protected function getApiKey(): ?string
    {
        return Config::get('services.openai.key');
    }
    
    /**
     * Upload a file to OpenAI
     * 
     * @param UploadedFile $file
     * @param string $purpose
     * @return array
     */
    public function uploadFile(UploadedFile $file, string $purpose = 'assistants')
    {
        try {
            // Store the file locally first
            $path = $file->storeAs(
                $this->storagePath,
                $file->hashName(),
                $this->diskName
            );
            
            if (!$path) {
                return [
                    'success' => false,
                    'message' => 'Failed to store file locally',
                ];
            }
            
            $fullPath = Storage::disk($this->diskName)->path($path);
            
            // Upload file to OpenAI
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
            ])->attach(
                'file', file_get_contents($fullPath), $file->getClientOriginalName()
            )->post("{$this->baseUrl}/files", [
                'purpose' => $purpose,
            ]);
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                // Save the file ID and other metadata to track in a local database if needed
                $fileData = [
                    'file_id' => $responseData['id'],
                    'filename' => $file->getClientOriginalName(),
                    'purpose' => $purpose,
                    'bytes' => $responseData['bytes'],
                    'created_at' => $responseData['created_at'],
                    'local_path' => $path,
                ];
                
                return [
                    'success' => true,
                    'data' => $fileData,
                ];
            } else {
                Log::error('OpenAI file upload error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'file' => $file->getClientOriginalName(),
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown file upload error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI file upload failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);
            
            return [
                'success' => false,
                'message' => 'File upload failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * List all files
     * 
     * @param string|null $purpose
     * @return array
     */
    public function listFiles(?string $purpose = null)
    {
        try {
            $queryParams = [];
            if ($purpose) {
                $queryParams['purpose'] = $purpose;
            }
            
            $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/files{$queryString}");
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData['data'] ?? [],
                ];
            } else {
                Log::error('OpenAI file listing error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown file listing error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI file listing failed', [
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'File listing failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Delete a file from OpenAI
     * 
     * @param string $fileId
     * @param bool $deleteLocal
     * @param string|null $localPath
     * @return array
     */
    public function deleteFile(string $fileId, bool $deleteLocal = true, ?string $localPath = null)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
                'Content-Type' => 'application/json',
            ])->delete("{$this->baseUrl}/files/{$fileId}");
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                // Delete local file if requested and path is provided
                if ($deleteLocal && $localPath) {
                    if (Storage::disk($this->diskName)->exists($localPath)) {
                        Storage::disk($this->diskName)->delete($localPath);
                    }
                }
                
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI file deletion error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'file_id' => $fileId,
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown file deletion error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI file deletion failed', [
                'error' => $e->getMessage(),
                'file_id' => $fileId,
            ]);
            
            return [
                'success' => false,
                'message' => 'File deletion failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Retrieve file information from OpenAI
     * 
     * @param string $fileId
     * @return array
     */
    public function getFile(string $fileId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/files/{$fileId}");
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI file retrieval error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'file_id' => $fileId,
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown file retrieval error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI file retrieval failed', [
                'error' => $e->getMessage(),
                'file_id' => $fileId,
            ]);
            
            return [
                'success' => false,
                'message' => 'File retrieval failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Retrieve file content from OpenAI
     * 
     * @param string $fileId
     * @return array
     */
    public function getFileContent(string $fileId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/files/{$fileId}/content");
            
            // For file content, check if the response is successful but handle the content as raw data
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->body(),
                ];
            } else {
                $responseData = $response->json();
                Log::error('OpenAI file content retrieval error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'file_id' => $fileId,
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown file content retrieval error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI file content retrieval failed', [
                'error' => $e->getMessage(),
                'file_id' => $fileId,
            ]);
            
            return [
                'success' => false,
                'message' => 'File content retrieval failed: ' . $e->getMessage(),
            ];
        }
    }
}