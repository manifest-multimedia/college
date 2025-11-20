<?php

namespace App\Http\Controllers\Api\Communication;

use App\Http\Controllers\Controller;
use App\Services\Communication\Chat\Document\DocumentUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ChatDocumentController extends Controller
{
    /**
     * The Document Upload Service instance.
     */
    protected DocumentUploadService $documentService;

    /**
     * Create a new controller instance.
     */
    public function __construct(DocumentUploadService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Upload a document to a chat session.
     */
    public function upload(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpeg,jpg,png,gif,txt,csv|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->documentService->uploadDocument(
                $request->input('session_id'),
                $request->file('file')
            );

            if (! $result['success']) {
                return response()->json($result, 500);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Failed to upload document', [
                'error' => $e->getMessage(),
                'session_id' => $request->input('session_id'),
                'file' => $request->file('file') ? $request->file('file')->getClientOriginalName() : null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a signed URL for downloading a document.
     */
    public function getDownloadUrl(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $path = $request->input('path');

            if (! Storage::disk('s3')->exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found',
                ], 404);
            }

            // Generate a temporary signed URL that expires after 60 minutes
            $url = Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(60));

            return response()->json([
                'success' => true,
                'url' => $url,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get download URL', [
                'error' => $e->getMessage(),
                'path' => $request->input('path'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get download URL: '.$e->getMessage(),
            ], 500);
        }
    }
}
