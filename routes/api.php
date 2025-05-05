<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ExamTicketController;
use App\Http\Controllers\Api\Communication\SmsController;
use App\Http\Controllers\Api\Communication\EmailController;
use App\Http\Controllers\Api\Communication\ChatController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Exam Ticket Validation API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('exam-tickets')->group(function () {
    Route::post('/validate', [ExamTicketController::class, 'validateTicket']);
    Route::post('/info', [ExamTicketController::class, 'ticketInfo']);
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
    });
});
