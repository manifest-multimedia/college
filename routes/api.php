<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ExamTicketController;
use App\Http\Controllers\Api\Communication\SmsController;
use App\Http\Controllers\Api\Communication\EmailController;
use App\Http\Controllers\Api\Communication\ChatController;
use App\Http\Controllers\Api\Communication\ChatDocumentController;
use App\Http\Controllers\Api\MemoController;
use App\Http\Controllers\Api\ExamClearanceController;
use App\Http\Controllers\Api\OfflineExamController;
use App\Http\Controllers\Api\ExamEntryTicketController;
use App\Http\Controllers\Api\ExamTimerController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Exam System API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // Offline Exam Routes
    Route::apiResource('offline-exams', OfflineExamController::class);
    Route::post('offline-exams/{id}/process-clearance', [OfflineExamController::class, 'processClearance']);
    
    // Exam Clearance Routes
    Route::get('exam-clearances', [ExamClearanceController::class, 'index']);
    Route::get('exam-clearances/{id}', [ExamClearanceController::class, 'show']);
    Route::put('exam-clearances/{id}', [ExamClearanceController::class, 'update']);
    Route::post('exam-clearances/check', [ExamClearanceController::class, 'checkClearance']);
    Route::post('exam-clearances/manual-override', [ExamClearanceController::class, 'manualOverride']);
    Route::post('exam-clearances/bulk-process', [ExamClearanceController::class, 'bulkProcess']);
    Route::get('students/{studentId}/clearances', [ExamClearanceController::class, 'getStudentClearances']);
    
    // Exam Entry Ticket Routes
    Route::apiResource('exam-entry-tickets', ExamEntryTicketController::class)->except(['update', 'destroy']);
    Route::post('exam-entry-tickets/bulk-issue', [ExamEntryTicketController::class, 'bulkIssue']);
    Route::post('exam-entry-tickets/{id}/verify', [ExamEntryTicketController::class, 'verifyTicket']);
    Route::get('students/{studentId}/tickets', [ExamEntryTicketController::class, 'getStudentTickets']);
});

/*
|--------------------------------------------------------------------------
| Exam Ticket Validation API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('exam-tickets')->group(function () {
    Route::post('/validate', [ExamEntryTicketController::class, 'validateTicket']);
    Route::post('/info', [ExamEntryTicketController::class, 'ticketInfo']);
});

/*
|--------------------------------------------------------------------------
| Communication Module API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // SMS Routes
    Route::prefix('communication/sms')->group(function () {
        Route::post('/send-single', [SmsController::class, 'sendSingle']);
        Route::post('/send-bulk', [SmsController::class, 'sendBulk']);
        Route::post('/send-to-group', [SmsController::class, 'sendToGroup']);
        Route::get('/logs', [SmsController::class, 'getLogs']);
        Route::get('/recipient-lists', [SmsController::class, 'getRecipientLists']);
    });

    // Email Routes
    Route::prefix('communication/email')->group(function () {
        Route::post('/send-single', [EmailController::class, 'sendSingle']);
        Route::post('/send-bulk', [EmailController::class, 'sendBulk']);
        Route::post('/send-to-group', [EmailController::class, 'sendToGroup']);
        Route::get('/logs', [EmailController::class, 'getLogs']);
        Route::get('/recipient-lists', [EmailController::class, 'getRecipientLists']);
    });

    // Chat Routes
    Route::prefix('communication/chat')->group(function () {
        Route::post('/sessions', [ChatController::class, 'createSession']);
        Route::get('/sessions/{sessionId}', [ChatController::class, 'getSession']);
        Route::post('/messages', [ChatController::class, 'sendMessage']);
        Route::get('/sessions/{sessionId}/messages', [ChatController::class, 'getMessageHistory']);
        Route::patch('/sessions/{sessionId}/status', [ChatController::class, 'updateSessionStatus']);
        Route::get('/sessions', [ChatController::class, 'getUserSessions']);
        
        // Document upload routes
        Route::post('/upload', [ChatDocumentController::class, 'upload']);
        Route::post('/document/download-url', [ChatDocumentController::class, 'getDownloadUrl']);
        
        // Typing status route
        Route::post('/typing', [ChatController::class, 'updateTypingStatus']);
    });
    
    /*
    |--------------------------------------------------------------------------
    | Memo Management API Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('memos')->group(function () {
        // Basic CRUD operations
        Route::get('/', [MemoController::class, 'index']);
        Route::post('/', [MemoController::class, 'store']);
        Route::get('/{id}', [MemoController::class, 'show']);
        Route::put('/{id}', [MemoController::class, 'update']);
        
        // Memo workflow actions
        Route::post('/{id}/forward', [MemoController::class, 'forward']);
        Route::post('/{id}/approve', [MemoController::class, 'approve']);
        Route::post('/{id}/reject', [MemoController::class, 'reject']);
        Route::post('/{id}/complete', [MemoController::class, 'complete']);
        
        // Procurement workflow actions
        Route::post('/{id}/procured', [MemoController::class, 'markAsProcured']);
        Route::post('/{id}/delivered', [MemoController::class, 'markAsDelivered']);
        Route::post('/{id}/audited', [MemoController::class, 'markAsAudited']);
        
        // Attachment management
        Route::delete('/{id}/attachments/{attachmentId}', [MemoController::class, 'deleteAttachment']);
    });
});

/*
|--------------------------------------------------------------------------
| Exam Timer API Routes
|--------------------------------------------------------------------------
*/

Route::post('/exam-timer/status', [ExamTimerController::class, 'checkStatus']);
Route::post('/exam-timer/extra-time', [ExamTimerController::class, 'checkExtraTime']);
Route::post('/exam/submit', [ExamTimerController::class, 'submitExam']);
