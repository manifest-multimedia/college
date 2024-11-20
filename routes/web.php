<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportGenerator;

Route::get('/auth/callback', [AuthController::class, 'handleCallback'])->name('auth.callback');


// Link to Tutor Accessment Form: 
Route::get('/tutor-assessment', function () {
    return redirect("https://forms.gle/9EpmJY9fTDT6QaUw9");
});

Route::get('/generate', function () {
    generateStudentID();
    return redirect()->route('dashboard');
})->name('generate-student-ids');

Route::get('/sign-up', function () {
    return redirect('https://auth.pnmtc.edu.gh/register');
})->name('signup');

Route::get('/', function () {
    // If the user is authenticated, redirect to dashboard
    if (Auth::check()) {
        return redirect()->route('dashboard');  // Adjust 'dashboard' to your preferred route
    }

    // Otherwise, show the login page
    return view('login');
})->middleware('guest');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');


    Route::post('/generate/report', [ReportGenerator::class, 'generateReport'])->name('generate.report');
});
