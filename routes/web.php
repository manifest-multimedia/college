<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportGenerator;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Request;
use App\Models\Exam;
use Illuminate\Support\Facades\Artisan;
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
})->middleware('guest')->name('home');

// Authenticated Routes
Route::middleware([
    'auth:sanctum',
])->group(function () {

    Route::get('/portal', function () {
        return view('dashboard');
    })->name('dashboard');

    // Student-only routes
    Route::middleware(['role:Student'])->group(function() {
        Route::get('/student-information', function () {
            return view('students.information');
        })->name('student.information');
    });

    // Academic routes (Lecturer, Academic Officer, Administrator, Super Admin)
    Route::middleware(['role:Lecturer|Academic Officer|Administrator|Super Admin'])->group(function() {
        Route::get('exam-results', function () {
            return view('exams.correct-data');
        })->name('exam.results');

        Route::get('/import-results', function () {
            return view('exams.result-import');
        })->name('exam.result.import');
        
        Route::get('/edit-exam/{exam_slug}', ExamEdit::class)->name('exams.edit');

        Route::get('/exam-center', function () {
            return view('examcenter');
        })->name('examcenter');

        Route::get('/exam-sessions', \App\Livewire\ExamSessions::class)->name('examsessions');

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
        
        Route::get('track-responses', function () {
            return view('exams.track-responses');
        })->name('track-responses');

        Route::get('/course-import', function () {
            return view('courses.import');
        })->name('courses.import');
    });
    
    // Administrator routes
    Route::middleware(['role:Administrator|Super Admin|Academic Officer'])->group(function() {
        Route::get('/students', function () {
            return view('students');
        })->name('students');

        // Student Import Route
        Route::get('/students/import', [App\Http\Controllers\StudentImportController::class, 'index'])->name('students.import');

        // Student individual routes
        Route::get('/students/{student}', function ($student) {
            return view('students.show', ['studentId' => $student]);
        })->name('students.show');

        Route::get('/students/{student}/edit', function ($student) {
            return view('students.edit', ['studentId' => $student]);
        })->name('students.edit');
        
        Route::post('/generate/report', [ReportGenerator::class, 'generateReport'])->name('generate.report');

        // Settings Routes
        Route::middleware(['auth:sanctum', 'role:Super Admin|Administrator'])->prefix('settings')->group(function () {
            Route::get('/general', function () {
                return view('settings.general');
            })->name('settings.general');

            Route::get('/users', function () {
                return view('settings.users');
            })->name('settings.users');

            Route::get('/roles', function () {
                return view('settings.roles');
            })->name('settings.roles');

            Route::get('/permissions', function () {
                return view('settings.permissions');
            })->name('settings.permissions');

            Route::get('/backup', function () {
                return view('settings.backup');
            })->name('settings.backup');
            
            // Backup download route
            Route::get('/backup/download/{path}', function ($path) {
                $fullPath = Storage::disk('local')->path($path);
                if (Storage::disk('local')->exists($path)) {
                    return response()->download($fullPath, basename($path));
                }
                return back()->with('error', 'Backup file not found.');
            })->name('settings.backup.download')->where('path', '.*');

            // Department Management Routes
            Route::get('/departments', function () {
                return view('settings.departments');
            })->name('settings.departments');
            
            Route::get('/user-departments', function () {
                return view('settings.user-departments');
            })->name('settings.user-departments');
        });
    });

    // Routes available to all authenticated users
    Route::get('/support-center', function () {
        return redirect()->away('https://desk.zoho.eu/support/pnmtc');
    })->name('supportcenter');

    Route::get('/staffmail', function () {
        return redirect()->away('https://pnmtc.edu.gh/webmail');
    })->name('staffmail');

    Route::get('/reset-pass', function () {
        return view('users.password-reset');
    })->name('reset-pass');

    Route::get('/exam-clearance', function () {
        return view('exams.clearance');
    });

    // Exam Response Tracker - System Role Access Only
    Route::get('/exam-response-tracker', function () {
        return view('exams.response-tracker');
    })->middleware('role:Super Admin')->name('exam.response.tracker');

    /*
    |--------------------------------------------------------------------------
    | Election System Routes
    |--------------------------------------------------------------------------
    */

    // Admin Election Management Routes
    Route::middleware(['auth:sanctum', 'role_or_permission:Super Admin|Administrator|election management.manage elections'])->prefix('admin')->group(function () {
        // Election Management
        Route::get('/elections', \App\Livewire\ElectionManager::class)->name('elections');
        Route::get('/elections/{election}/positions', \App\Livewire\ElectionPositionManager::class)->name('election.positions');
        Route::get('/elections/{election}/candidates/{position}', \App\Livewire\ElectionCandidateManager::class)->name('election.candidates');
        Route::get('/elections/{election}/results', \App\Livewire\ElectionResultsDashboard::class)->name('election.results');
    });

    // Student Voting Routes
    Route::prefix('voting')->group(function () {
        Route::get('/{election}/verify', \App\Livewire\ElectionVoterVerification::class)->name('election.verify');
        Route::get('/{election}/vote/{sessionId?}', \App\Livewire\ElectionVoting::class)->name('election.vote');
        Route::get('/{election}/thank-you/{sessionId?}', \App\Livewire\ElectionThankYou::class)->name('election.thank-you');
        Route::get('/{election}/expired', \App\Livewire\ElectionExpired::class)->name('election.expired');
    });

    // Public Election Status Route (accessible to all authenticated users)
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/elections/active', \App\Livewire\ActiveElections::class)->name('elections.active');
    });

    /*
    |--------------------------------------------------------------------------
    | Finance Management & Fee Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'role:Super Admin|Administrator|Finance Officer'])->prefix('finance')->group(function () {
        Route::get('/billing', function () {
            return view('finance.billing');
        })->name('finance.billing');

        Route::get('/manager', function(){
            return view('test-view');
        })->name('manafter');

        Route::get('/exam-clearance', function () {
            return view('finance.exam-clearance');
        })->name('finance.exam.clearance');
        
        Route::get('/qr-scanner', \App\Livewire\Finance\ExamTicketScanner::class)->name('finance.exam.scanner');
        
        Route::get('/course-registration/{studentId?}', function ($studentId = null) {
            return view('finance.course-registration', ['studentId' => $studentId]);
        })->name('finance.course.registration');
        
        Route::get('/payments', \App\Livewire\Finance\FeePaymentManager::class)->name('finance.payments');
        
        Route::get('/reports', \App\Livewire\Finance\FinancialReportsManager::class)->name('finance.reports');
        
        Route::get('/fee-types', \App\Livewire\Finance\FeeTypesManager::class)->name('finance.fee.types');
        
        Route::get('/fee-structure', \App\Livewire\Finance\FeeStructureManager::class)->name('finance.fee.structure');
        
        Route::get('/exam-tickets-manager',function(){
            return view('finance.exam-tickets-manager');
        })->name('finance.exam.tickets.manager');
        
        Route::get('/exam-tickets/{clearanceId}', function ($clearanceId) {
            return view('finance.exam-tickets', ['clearanceId' => $clearanceId]);
        })->name('finance.exam.tickets');
        
        // Legacy payment routes
        Route::get('/payment/record/{billId}', function ($billId) {
            return view('finance.payment-record', ['billId' => $billId]);
        })->name('payment.record');
        
        Route::get('/bill/details/{billId}', function ($billId) {
            return view('finance.bill-details', ['billId' => $billId]);
        })->name('bill.details');
        
        Route::get('/bill/view/{id}', function ($id) {
            return view('finance.bill-details', ['billId' => $id]);
        })->name('finance.bill.view');
        
        Route::get('/bill/print/{billId}', function ($billId) {
            return view('finance.bill-print', ['billId' => $billId]);
        })->name('bill.print');
        
        Route::get('/exam-tickets/{clearanceId}', function ($clearanceId) {
            return view('finance.exam-tickets', ['clearanceId' => $clearanceId]);
        })->name('finance.exam.tickets');
        
        Route::get('/ticket/print/{ticketId}', function ($ticketId) {
            return view('finance.ticket-print', ['ticketId' => $ticketId]);
        })->name('finance.exam.ticket.print');
    });
    
    

    /*
    |--------------------------------------------------------------------------
    | Course Registration Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/course-registration', function () {
            return view('course.registration');
        })->name('courseregistration');
        
        Route::get('/course-registration/history', function () {
            return view('course.registration-history');
        })->name('courseregistration.history');
        
        Route::get('/course-registration/approvals', function () {
            return view('course.registration-approvals');
        })->name('courseregistration.approvals');
    });

    /*
    |--------------------------------------------------------------------------
    | Support Center Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum'])->prefix('support')->group(function () {
        Route::get('/tickets', function () {
            return view('support.tickets');
        })->name('support.tickets');
        
        Route::get('/knowledgebase', function () {
            return view('support.knowledgebase');
        })->name('support.knowledgebase');
    });

    /*
    |--------------------------------------------------------------------------
    | Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'role:Super Admin|Administrator'])->prefix('settings')->group(function () {
        Route::get('/general', function () {
            return view('settings.general');
        })->name('settings.general');
        
        Route::get('/users', function () {
            return view('settings.users');
        })->name('settings.users');
        
        Route::get('/roles', function () {
            return view('settings.roles');
        })->name('settings.roles');
        
        Route::get('/permissions', function () {
            return view('settings.permissions');
        })->name('settings.permissions');
        
        Route::get('/backup', function () {
            return view('settings.backup');
        })->name('settings.backup');
        
        // Department Management Routes
        Route::get('/departments', function () {
            return view('settings.departments');
        })->name('settings.departments');
        
        Route::get('/user-departments', function () {
            return view('settings.user-departments');
        })->name('settings.user-departments');
    });

    /*
    |--------------------------------------------------------------------------
    | Academics Module Routes 'permission:manage-academics'
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum'])->prefix('academics')->name('academics.')
    ->group(function () {

        // Dashboard
        Route::get('/', function () {
            return view('academics.dashboard');
        })->name('dashboard');

        
        // Settings
        Route::get('/settings', [App\Http\Controllers\AcademicSettingsController::class, 'index'])->name('settings.index');

        Route::post('/settings', [App\Http\Controllers\AcademicSettingsController::class, 'update'])->name('settings.update');
        
        // Academic Years
        Route::resource('academic-years', App\Http\Controllers\AcademicYearController::class);
        
        // Semesters
        Route::resource('semesters', App\Http\Controllers\SemesterController::class);
        Route::patch('/semesters/{semester}/toggle-active', [App\Http\Controllers\SemesterController::class, 'toggleActive'])->name('semesters.toggle-active');
        
        // College Classes
        Route::resource('classes', App\Http\Controllers\CollegeClassController::class);
        Route::post('/classes/filter', [App\Http\Controllers\CollegeClassController::class, 'filter'])->name('classes.filter');
        
        // Cohorts
        Route::resource('cohorts', App\Http\Controllers\CohortController::class);
        
        // Grade Types
        Route::resource('grades', App\Http\Controllers\GradeController::class);
        
        // Student Grades
        Route::resource('student-grades', App\Http\Controllers\StudentGradeController::class);
        Route::post('/student-grades/filter', [App\Http\Controllers\StudentGradeController::class, 'filter'])->name('student-grades.filter');
        Route::get('/classes/{class}/batch-grades', [App\Http\Controllers\StudentGradeController::class, 'batchCreate'])->name('classes.batch-grades');
        Route::post('/classes/{class}/batch-grades', [App\Http\Controllers\StudentGradeController::class, 'batchStore'])->name('classes.batch-grades.store');

        // Exam Types
        Route::get('/exam-types', App\Livewire\Academics\ExamTypeManager::class)->name('exam-types');

        // Year migration command (for admins only)
        Route::post('/migrate-year-data', function () {
            if (auth()->user()->hasRole('admin')) {
                Artisan::call('academics:migrate-year-data');
                return back()->with('success', 'Year data migration completed.');
            }
            return back()->with('error', 'Unauthorized operation.');
        })->name('migrate-year-data');
    });

    /*
    |--------------------------------------------------------------------------
    | Communication Module Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->prefix('communication')->group(function () {
        Route::get('/sms', \App\Livewire\Communication\SendSms::class)->name('communication.sms');
        Route::get('/email', \App\Livewire\Communication\SendEmail::class)->name('communication.email');
        Route::get('/chat', \App\Livewire\Communication\Chat::class)->name('communication.chat');
        
        // Document preview and download routes
        Route::get('/chat/document/preview/{path}', [\App\Http\Controllers\Communication\ChatDocumentPreviewController::class, 'preview'])
            ->name('chat.document.preview')
            ->where('path', '.*');
        Route::get('/chat/document/download/{path}', [\App\Http\Controllers\Communication\ChatDocumentPreviewController::class, 'download'])
            ->name('chat.document.download')
            ->where('path', '.*');
    });

    /*
    |--------------------------------------------------------------------------
    | Memo Management System Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum'])->prefix('memos')->group(function () {
        // Main memo listing and dashboard
        Route::get('/', \App\Livewire\Memos\MemoList::class)->name('memos');
        
        // Create new memo
        Route::get('/create', \App\Livewire\Memos\CreateMemo::class)->name('memo.create');
        
        // View memo details
        Route::get('/{id}', \App\Livewire\Memos\ViewMemo::class)->name('memo.view');
        
        // Edit memo (only for draft memos)
        Route::get('/{id}/edit', \App\Livewire\Memos\CreateMemo::class)->name('memo.edit');
    });

    /*
    |--------------------------------------------------------------------------
    | Exam Module Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
        // Offline Exams Management
        Route::get('/exams/offline', function () {
            return view('admin.exam.offline-exams');
        })->middleware('permission:view exams')->name('exams.offline');
        
        // Exam Clearances Management
        Route::get('/exams/clearances', function () {
            return view('admin.exam.clearances');
        })->name('clearances.index');
        
        // Entry Tickets Management
        Route::get('/exams/tickets', function () {
            return view('admin.exam.tickets');
        })->name('tickets.index');
    });

    // Exam Extra Time Management (System role only)
    Route::middleware(['auth:sanctum', 'role:System'])->group(function () {
        Route::get('/admin/exam-extra-time', function () {
            return view('exams.extra-time');
        })->name('admin.exam-extra-time');
    });

});
