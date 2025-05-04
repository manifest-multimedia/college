<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ExamTicketController;

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
