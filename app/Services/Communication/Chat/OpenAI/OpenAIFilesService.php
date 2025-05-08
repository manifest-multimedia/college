<?php

namespace App\Services\Communication\Chat\OpenAI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class OpenAIFilesService
{
    /**
     * The API URL for OpenAI Files endpoints
     */
    protected string $apiUrl;
    
    /**
     * The API key for OpenAI
     */
    protected string $apiKey;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apiKey = Config::get('services.openai.key');
        $this->apiUrl = Config::get('services.openai.files_url', 'https://api.openai.com/v1/files');
        
        if (empty($this->apiKey)) {
            throw new \Exception('OpenAI API key not configured');
        }
    }
    
    /**
     * Upload a file to OpenAI Files API
     * 
     * @param string $filePath The path to the file
     * @param string $purpose The purpose of the file (default: 'assistants')
     * @return array
     */
    public function uploadFile(string $filePath, string $purpose = 'assistants'): array
    {
        try {
            // Check if file exists
            if (!file_exists($filePath)) {
                throw new \Exception("File not found: {$filePath}");
            }
            
            // Create multipart request
            $response = Http::withToken($this->apiKey)
                ->attach('file', fopen($filePath, 'r'), basename($filePath))
                ->post("{$this->apiUrl}", [
                    'purpose' => $purpose,
                ]);
            
            if ($response->successful()) {
                $result = $response->json();
                Log::info('File uploaded to OpenAI', [
                    'file_id' => $result['id'],
                    'filename' => basename($filePath)
                ]);
                
                return [
                    'success' => true,
                    'file_id' => $result['id'],
                    'filename' => $result['filename'],
                    'purpose' => $result['purpose'],
                    'created_at' => $result['created_at']
                ];
            } else {
                $error = $response->json();
                Log::error('OpenAI file upload failed', [
                    'status' => $response->status(),
                    'error' => $error
                ]);
                
                return [
                    'success' => false,
                    'message' => $error['error']['message'] ?? 'Unknown error',
                    'status' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI file upload exception', [
                'error' => $e->getMessage(),
                'file' => basename($filePath)
            ]);
            
            return [
                'success' => false,
                'message' => 'File upload failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Retrieve a list of files
     * 
     * @return array
     */
    public function listFiles(): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->get($this->apiUrl);
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'files' => $response->json()['data']
                ];
            } else {
                $error = $response->json();
                Log::error('OpenAI list files failed', [
                    'status' => $response->status(),
                    'error' => $error
                ]);
                
                return [
                    'success' => false,
                    'message' => $error['error']['message'] ?? 'Unknown error'
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI list files exception', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to list files: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Retrieve a specific file by ID
     * 
     * @param string $fileId The file ID
     * @return array
     */
    public function getFile(string $fileId): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->get("{$this->apiUrl}/{$fileId}");
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'file' => $response->json()
                ];
            } else {
                $error = $response->json();
                Log::error('OpenAI get file failed', [
                    'status' => $response->status(),
                    'error' => $error,
                    'file_id' => $fileId
                ]);
                
                return [
                    'success' => false,
                    'message' => $error['error']['message'] ?? 'Unknown error'
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI get file exception', [
                'error' => $e->getMessage(),
                'file_id' => $fileId
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to get file: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Retrieve a specific file by ID (alias for getFile for API naming consistency)
     * 
     * @param string $fileId The file ID
     * @return array
     */
    public function retrieveFile(string $fileId): array
    {
        return $this->getFile($fileId);
    }
    
    /**
     * Delete a file from OpenAI
     * 
     * @param string $fileId The file ID
     * @return array
     */
    public function deleteFile(string $fileId): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->delete("{$this->apiUrl}/{$fileId}");
            
            if ($response->successful()) {
                Log::info('OpenAI file deleted', [
                    'file_id' => $fileId
                ]);
                
                return [
                    'success' => true,
                    'deleted' => $response->json()['deleted']
                ];
            } else {
                $error = $response->json();
                Log::error('OpenAI delete file failed', [
                    'status' => $response->status(),
                    'error' => $error,
                    'file_id' => $fileId
                ]);
                
                return [
                    'success' => false,
                    'message' => $error['error']['message'] ?? 'Unknown error'
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI delete file exception', [
                'error' => $e->getMessage(),
                'file_id' => $fileId
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to delete file: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Retrieve file content
     * 
     * @param string $fileId The file ID
     * @return array
     */
    public function retrieveFileContent(string $fileId): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->get("{$this->apiUrl}/{$fileId}/content");
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'content' => $response->body()
                ];
            } else {
                $error = $response->json();
                Log::error('OpenAI retrieve file content failed', [
                    'status' => $response->status(),
                    'error' => $error,
                    'file_id' => $fileId
                ]);
                
                return [
                    'success' => false,
                    'message' => $error['error']['message'] ?? 'Unknown error'
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI retrieve file content exception', [
                'error' => $e->getMessage(),
                'file_id' => $fileId
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve file content: ' . $e->getMessage()
            ];
        }
    }
}