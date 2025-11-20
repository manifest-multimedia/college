<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChatDocumentPreviewController extends Controller
{
    /**
     * Display a preview of the document.
     */
    public function preview(Request $request, $path)
    {
        try {
            // Verify the user has access to this document
            $this->authorizeAccess($path);

            // Check if file exists
            if (! Storage::disk('public')->exists($path)) {
                abort(404, 'Document not found');
            }

            // Get file content and mime type
            $fileContent = Storage::disk('public')->get($path);
            $mimeType = Storage::disk('public')->mimeType($path);

            return response($fileContent)->header('Content-Type', $mimeType);
        } catch (\Exception $e) {
            Log::error('Failed to preview document', [
                'error' => $e->getMessage(),
                'path' => $path,
            ]);

            abort(500, 'Error loading document preview');
        }
    }

    /**
     * Generate a download link for the document.
     */
    public function download(Request $request, $path)
    {
        try {
            // Verify the user has access to this document
            $this->authorizeAccess($path);

            // Check if file exists
            if (! Storage::disk('public')->exists($path)) {
                abort(404, 'Document not found');
            }

            // Generate a URL for the file
            $url = Storage::disk('public')->url($path);

            return response()->json([
                'success' => true,
                'url' => $url,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate download link', [
                'error' => $e->getMessage(),
                'path' => $path,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate download link: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify that the authenticated user has access to the document.
     */
    protected function authorizeAccess($path)
    {
        $chatMessage = ChatMessage::where('file_path', $path)->firstOrFail();
        $session = ChatSession::findOrFail($chatMessage->chat_session_id);

        // Check if the user has access to this session
        if ($session->user_id !== Auth::id()) {
            abort(403, 'You do not have permission to access this document');
        }
    }
}
