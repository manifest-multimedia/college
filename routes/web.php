<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportGenerator;


Route::get('/mcq', function () {
    return redirect('https://docs.google.com/spreadsheets/d/1wJg55f1q6OjNj05yy47cL5RlcBOIM4hSCN7GINM-3To/edit?usp=sharing');
})->name('mcq');


Route::get('/auth/callback', [AuthController::class, 'handleCallback'])->name('auth.callback');

// Link to Tutor Assessment Form:
Route::get('/tutor-assessment', function () {
    return redirect("https://forms.gle/9EpmJY9fTDT6QaUw9");
});

// Generate Student IDs and redirect to dashboard
Route::get('/generate', function () {
    generateStudentID(); // Ensure this function exists
    return redirect()->route('dashboard');
})->name('generate-student-ids');

// Redirect to Sign-Up page
Route::get('/sign-up', function () {
    return redirect('https://auth.pnmtc.edu.gh/register');
})->name('signup');

// Root route handling
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login'); // Prefer named routes
})->middleware('guest');

// Authenticated Routes
Route::middleware([
    'auth:sanctum',
])->group(function () {
    Route::get('/admin', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/generate/report', [ReportGenerator::class, 'generateReport'])->name('generate.report');

    Route::get('/exam-center', function () {
        return view('examcenter');
    })->name('examcenter');
});
