<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportGenerator;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Request;
use App\Models\Exam;

use App\Http\Controllers\FileUploadController;
use App\Livewire\ExamEdit;

Route::get('/mcq', function () {
    return redirect('https://docs.google.com/spreadsheets/d/1wJg55f1q6OjNj05yy47cL5RlcBOIM4hSCN7GINM-3To/edit?usp=sharing');
})->name('mcq');

Route::post('/upload-file', [FileUploadController::class, 'upload'])->name('file.upload');


Route::get('/exams/{slug}/{student_id}', function ($slug, $student_id) {

    $exam = Exam::where('slug', $slug)->first();
    return view('frontend.exam', [
        'examPassword' => $exam->password,
        'student_id' => $student_id
    ]);
})->name('exams');



Route::get('/take-exam', function () {
    return view('frontend.take-exam');
})->name('take-exam');

Route::get('/extra-time', function () {
    return view('exams.extra-time');
})->name('extra-time');


Route::get('/auth/callback', [AuthController::class, 'handleCallback'])->name('auth.callback');

// Link to Tutor Assessment Form:
Route::get('/tutor-assessment', function () {
    return redirect()->away("https://forms.gle/9EpmJY9fTDT6QaUw9");
});

// Generate Student IDs and redirect to dashboard
Route::get('/generate', function () {
    generateStudentID(); // Ensure this function exists
    return redirect()->route('dashboard');
})->name('generate-student-ids');

// Redirect to Sign-Up page
Route::get('/sign-up', function () {
    return redirect('https://auth.pnmtc.edu.gh/sign-up');
})->name('signup');

// Root route handling
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login'); // Prefer named routes
})->middleware('guest');

// Authenticated Routes
Route::middleware([
    'auth:sanctum',
])->group(function () {


    Route::get('/student-information', function () {
        return view('students.information');
    })->name('student.information');


    Route::get('exam-results', function () {
        return view('exams.correct-data');
    })->name('exam.results');

    Route::get('/import-results', function () {
        return view('exams.result-import');
    })->name('exam.result.import');

    Route::get('/portal', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/students', function () {
        return view('students');
    })->name('students');

    Route::get('/course-import', function () {
        return view('courses.import');
    })->name('courses.import');

    Route::post('/generate/report', [ReportGenerator::class, 'generateReport'])->name('generate.report');


    Route::get('/edit-exam/{exam_slug}', ExamEdit::class)->name('exams.edit');

    Route::get('/exam-center', function () {
        return view('examcenter');
    })->name('examcenter');

    Route::get('/create-exam', function () {
        return view('exams.create');
    })->name('exams.create');


    Route::get('/question-bank', function () {
        return view('questionbank');
    })->name('questionbank');

    Route::get('/question-bank/{slug}', function ($slug) {
        // Get Id passed int via route('questionbank, $exam->id);
        $exam_id = Exam::where('slug', $slug)->first()->id;

        return view('questionbank', compact('exam_id'));
    })->name('questionbank.with.slug');

    Route::get('/support-center', function () {
        return redirect()->away('https://desk.zoho.eu/support/pnmtc');
    })->name('supportcenter');

    Route::get('/staffmail', function () {
        return redirect()->away('https://pnmtc.edu.gh/webmail');
    })->name('staffmail');

    Route::get('/reset-pass', function () {
        return view('users.password-reset');
    })->name('reset-pass');

    Route::get('track-responses', function () {
        return view('exams.track-responses');
    })->name('track-responses');

    Route::get('/exam-clearance', function () {
        return view('exams.clearance');
    });




    // Test Email route to send mail to johnson@pnmtc.edu.gh
    // Route::get('/test-email', function () {
    //     Mail::to('johnson@manifestghana.com')->send(new WelcomeEmail);
    //     return redirect()->route('dashboard');
    // })->name('test-email');





});
