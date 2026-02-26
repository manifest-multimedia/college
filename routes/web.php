<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\ReportGenerator;
use App\Livewire\ExamEdit;
use App\Models\Exam;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/mcq', function () {
    return redirect('https://docs.google.com/spreadsheets/d/1wJg55f1q6OjNj05yy47cL5RlcBOIM4hSCN7GINM-3To/edit?usp=sharing');
})->name('mcq');

Route::post('/upload-file', [FileUploadController::class, 'upload'])->name('file.upload');

// Exam Results Routes (MUST be before wildcard /exams/{slug}/{student_id} route to avoid conflicts)
Route::middleware(['auth:sanctum', 'role:System|Academic Officer|Administrator|Lecturer'])->group(function () {
    Route::get('/exams/results', [App\Http\Controllers\Admin\ExamResultsController::class, 'index'])
        ->name('exams.results');

    Route::get('/exams/results/get', [App\Http\Controllers\Admin\ExamResultsController::class, 'getResults'])
        ->name('admin.exam-results.get');

    Route::get('/exams/results/export/excel', [App\Http\Controllers\Admin\ExamResultsController::class, 'exportExcel'])
        ->name('admin.exam-results.export.excel');

    Route::get('/exams/results/export/pdf', [App\Http\Controllers\Admin\ExamResultsController::class, 'exportPDF'])
        ->name('admin.exam-results.export.pdf');
});

// Authentication Routes
Route::get('/auth/callback', [AuthController::class, 'handleCallback'])->name('auth.callback');

// Regular Authentication Routes (only available when AUTH_METHOD=regular)
Route::middleware('guest')->group(function () {
    Route::post('/regular-login', [App\Http\Controllers\RegularAuthController::class, 'login'])->name('regular.login');

    // Staff Registration routes (only if enabled in config)
    Route::get('/staff/register', [App\Http\Controllers\RegularAuthController::class, 'showStaffRegistrationForm'])->name('staff.register');
    Route::post('/staff/register', [App\Http\Controllers\RegularAuthController::class, 'registerStaff']);

    // Student Registration routes (enabled for both auth methods)
    Route::get('/students/register', [App\Http\Controllers\RegularAuthController::class, 'showStudentRegistrationForm'])->name('students.register');
    Route::post('/students/register', [App\Http\Controllers\RegularAuthController::class, 'registerStudent']);

    // Legacy register route - redirect to staff registration
    Route::get('/register', function () {
        return redirect()->route('staff.register');
    })->name('register');
});

// Logout route (available for both auth methods)
Route::post('/logout', [App\Http\Controllers\RegularAuthController::class, 'logout'])->middleware('auth')->name('logout');

// Password Reset Routes (only available when AUTH_METHOD=regular)
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [App\Http\Controllers\PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [App\Http\Controllers\PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [App\Http\Controllers\PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [App\Http\Controllers\PasswordResetController::class, 'reset'])->name('password.update');
});

// Link to Tutor Assessment Form:
Route::get('/tutor-assessment', function () {
    return redirect()->away('https://forms.gle/9EpmJY9fTDT6QaUw9');
});

// Dynamic Sign-Up route based on authentication method (defaults to staff registration)
Route::get('/sign-up', function () {
    $authService = app(\App\Services\AuthenticationService::class);

    if ($authService->isAuthCentral()) {
        $signupUrl = $authService->getSignupUrl();
        if ($signupUrl && filter_var($signupUrl, FILTER_VALIDATE_URL)) {
            return redirect()->away($signupUrl);
        }

        return redirect()->route('login')->withErrors(['signup' => 'Registration is not available.']);
    }

    // For regular auth, default to staff registration
    return redirect()->route('staff.register');
})->name('signup');

// Student Registration route (AuthCentral only)
Route::get('/student-registration', function () {
    $authService = app(\App\Services\AuthenticationService::class);

    if (! $authService->isAuthCentral()) {
        return redirect()->route('login')->withErrors(['registration' => 'Student registration is only available with AuthCentral.']);
    }

    $registrationUrl = $authService->getStudentRegistrationUrl();
    if ($registrationUrl) {
        return redirect()->away($registrationUrl);
    }

    return redirect()->route('login')->withErrors(['registration' => 'Student registration is not available.']);
})->name('student.registration');

// Root route handling
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login'); // Prefer named routes
})->middleware('guest')->name('home');

// Authenticated Routes
Route::middleware([
    'auth:sanctum',
])->group(function () {

    // Impersonation Routes
    Route::post('/impersonate/{user}', [\App\Http\Controllers\ImpersonationController::class, 'start'])
        ->middleware('role:System')
        ->name('impersonate.start');

    Route::match(['POST', 'GET'], '/impersonate/stop', [\App\Http\Controllers\ImpersonationController::class, 'stop'])
        ->name('impersonate.stop');

    Route::get('/portal', function () {
        // Check if user has Student role and redirect to student dashboard
        if (auth()->user()->hasRole('Student')) {
            return redirect()->route('student.dashboard');
        }

        // Otherwise, show the general dashboard for staff/admin users
        return view('dashboard');
    })->name('dashboard');

    // Student-only routes
    Route::middleware(['role:Student'])->group(function () {
        Route::get('/student-dashboard', [App\Http\Controllers\StudentDashboardController::class, 'index'])->name('student.dashboard');

        Route::get('/student-information', function () {
            return view('students.information');
        })->name('student.information');

        // Student Assessment Scores
        Route::get('/student/assessment-scores', [App\Http\Controllers\Student\AssessmentScoresController::class, 'index'])->name('student.assessment-scores');
        Route::get('/student/assessment-scores/get', [App\Http\Controllers\Student\AssessmentScoresController::class, 'getScores'])->name('student.assessment-scores.get');
        Route::get('/student/assessment-scores/pdf', [App\Http\Controllers\Student\AssessmentScoresController::class, 'exportPdf'])->name('student.assessment-scores.pdf');
    });

    // Academic routes (Lecturer, Academic Officer, Administrator, Super Admin, Finance Manager)
    Route::middleware(['role:Lecturer|Academic Officer|Administrator|Super Admin|System|Finance Manager'])->group(function () {
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

        // DEPRECATED: Exam Sessions route - This functionality is now handled by the Exam Audit Tool
        // Route::get('/exam-sessions', \App\Livewire\ExamSessions::class)->name('examsessions');

        // Exam Management Routes - IMPORTANT: These specific routes must come BEFORE the wildcard route '/exams/{exam}'
        Route::get('/create-exam', [App\Http\Controllers\ExamController::class, 'create'])->name('exams.create');
        Route::post('/exams', [App\Http\Controllers\ExamController::class, 'store'])->name('exams.store');
        Route::get('/exams/get-courses', [App\Http\Controllers\ExamController::class, 'getCourses'])->name('exams.get-courses');
        Route::get('/exams/get-question-sets', [App\Http\Controllers\ExamController::class, 'getQuestionSets'])->name('exams.get-question-sets');
        Route::get('/exams/generate-password', [App\Http\Controllers\ExamController::class, 'generatePassword'])->name('exams.generate-password');
        Route::post('/exams/validate-form', [App\Http\Controllers\ExamController::class, 'validateForm'])->name('exams.validate-form');

        // Wildcard route - MUST be after specific /exams/* routes
        Route::get('/exams/{exam}', \App\Livewire\ExamDetail::class)->name('exams.show');

        Route::get('/question-bank', function () {
            return view('questionbank');
        })->name('questionbank');

        Route::get('/question-bank/{slug}', function ($slug) {
            // Get Id passed int via route('questionbank, $exam->id);
            $exam_id = Exam::where('slug', $slug)->first()->id;

            return view('questionbank', compact('exam_id'));
        })->name('questionbank.with.slug');

        // Enhanced Question Set Management Routes
        Route::get('/question-sets', function () {
            return view('question-sets');
        })->name('question.sets');

        Route::get('/question-sets/create', function () {
            return view('question-sets.create');
        })->name('question.sets.create');

        Route::get('/question-sets/{id}', function ($id) {
            return view('question-sets.show', compact('id'));
        })->name('question.sets.show');

        Route::get('/question-sets/{id}/edit', function ($id) {
            return view('question-sets.edit', compact('id'));
        })->name('question.sets.edit');

        Route::get('/question-sets/{id}/questions', function ($id) {
            return view('question-sets.questions', compact('id'));
        })->name('question.sets.questions');

        // Question Set Bulk Import Routes
        Route::get('/question-sets/{id}/import', [App\Http\Controllers\QuestionSetImportController::class, 'index'])->name('question.sets.import');
        Route::post('/question-sets/{id}/import/columns', [App\Http\Controllers\QuestionSetImportController::class, 'detectColumns'])->name('question.sets.import.columns');
        Route::post('/question-sets/{id}/import/preview', [App\Http\Controllers\QuestionSetImportController::class, 'preview'])->name('question.sets.import.preview');
        Route::post('/question-sets/{id}/import', [App\Http\Controllers\QuestionSetImportController::class, 'store'])->name('question.sets.import.process');

        // Individual Question Management Routes
        Route::get('/question-sets/{id}/questions/create', [App\Http\Controllers\QuestionSetQuestionController::class, 'create'])->name('question.sets.questions.create');
        Route::post('/question-sets/{id}/questions', [App\Http\Controllers\QuestionSetQuestionController::class, 'store'])->name('question.sets.questions.store');
        Route::get('/question-sets/{id}/questions/{questionId}/edit', [App\Http\Controllers\QuestionSetQuestionController::class, 'edit'])->name('question.sets.questions.edit');
        Route::get('/question-sets/{id}/questions/{questionId}', [App\Http\Controllers\QuestionSetQuestionController::class, 'show'])->name('question.sets.questions.show');
        Route::put('/question-sets/{id}/questions/{questionId}', [App\Http\Controllers\QuestionSetQuestionController::class, 'update'])->name('question.sets.questions.update');
        Route::delete('/question-sets/{id}/questions/{questionId}', [App\Http\Controllers\QuestionSetQuestionController::class, 'destroy'])->name('question.sets.questions.destroy');

        Route::get('/question-import-export', function () {
            return redirect()->route('question.sets')->with('info', 'Please select a question set first, then use the Import button to import questions.');
        })->name('question.import.export');

        Route::get('track-responses', function () {
            return view('exams.track-responses');
        })->name('track-responses');

        Route::get('/course-import', function () {
            return view('courses.import');
        })->name('courses.import');
    });

    // Assessment Scores - Accessible by teaching and administrative staff (all roles except Student)
    Route::middleware(['auth:sanctum', 'role:Administrator|Super Admin|Academic Officer|System|Finance Manager|Lecturer'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/assessment-scores', [App\Http\Controllers\Admin\AssessmentScoresController::class, 'index'])->name('assessment-scores.index');
        Route::get('/assessment-scores/get-courses', [App\Http\Controllers\Admin\AssessmentScoresController::class, 'getCourses'])->name('assessment-scores.get-courses');
        Route::post('/assessment-scores/load-scoresheet', [App\Http\Controllers\Admin\AssessmentScoresController::class, 'loadScoresheet'])->name('assessment-scores.load-scoresheet');
        Route::post('/assessment-scores/save-scores', [App\Http\Controllers\Admin\AssessmentScoresController::class, 'saveScores'])->name('assessment-scores.save-scores');
        Route::get('/assessment-scores/download-template', [App\Http\Controllers\Admin\AssessmentScoresController::class, 'downloadTemplate'])->name('assessment-scores.download-template');
        Route::post('/assessment-scores/import-excel', [App\Http\Controllers\Admin\AssessmentScoresController::class, 'importExcel'])->name('assessment-scores.import-excel');
        Route::post('/assessment-scores/confirm-import', [App\Http\Controllers\Admin\AssessmentScoresController::class, 'confirmImport'])->name('assessment-scores.confirm-import');
        Route::post('/assessment-scores/export-excel', [App\Http\Controllers\Admin\AssessmentScoresController::class, 'exportExcel'])->name('assessment-scores.export-excel');

        // Get courses by class
        Route::get('/courses/by-class', [App\Http\Controllers\Admin\AssessmentScoresController::class, 'getCoursesByClass'])->name('courses.by-class');
    });

    // Academic Officer Assessment Score Management
    Route::middleware(['auth:sanctum', 'role:Academic Officer|Super Admin'])->prefix('academic-officer')->name('academic-officer.')->group(function () {
        Route::get('/assessment-scores', [App\Http\Controllers\AcademicOfficer\AssessmentScoreManagementController::class, 'index'])->name('assessment-scores');
        Route::get('/assessment-scores/get', [App\Http\Controllers\AcademicOfficer\AssessmentScoreManagementController::class, 'getScores'])->name('assessment-scores.get');
        Route::post('/assessment-scores/{id}/toggle-publish', [App\Http\Controllers\AcademicOfficer\AssessmentScoreManagementController::class, 'togglePublish'])->name('assessment-scores.toggle-publish');
        Route::post('/assessment-scores/bulk-publish', [App\Http\Controllers\AcademicOfficer\AssessmentScoreManagementController::class, 'bulkPublish'])->name('assessment-scores.bulk-publish');
    });

    // Backward compatibility - redirect old route to new one
    Route::get('/assessment-scores', function () {
        return redirect()->route('admin.assessment-scores.index');
    })->middleware(['auth:sanctum', 'role:Administrator|Super Admin|Academic Officer|System|Finance Manager|Lecturer'])->name('assessment-scores');

    // Administrator routes
    Route::middleware(['role:Administrator|Super Admin|Academic Officer|System|Finance Manager'])->group(function () {
        Route::get('/students', function () {
            return view('students');
        })->name('students');

        // Student Import Route (must be before {student} route)
        Route::get('/students/import', [App\Http\Controllers\StudentImportController::class, 'index'])->name('students.import');

        // Student Create Route (must be before {student} route)
        Route::get('/students/create', function () {
            return view('students.create');
        })->name('students.create');

        // Student individual routes
        Route::get('/students/{student}', function ($student) {
            return view('students.show', ['studentId' => $student]);
        })->name('students.show');

        Route::get('/students/{student}/edit', function ($student) {
            return view('students.edit', ['studentId' => $student]);
        })->name('students.edit');

        Route::delete('/students/{student}', [\App\Http\Controllers\StudentController::class, 'destroy'])
            ->name('students.destroy');

        Route::post('/generate/report', [ReportGenerator::class, 'generateReport'])->name('generate.report');

        // Settings Routes
        Route::middleware(['auth:sanctum', 'role:Super Admin|Administrator|System'])->prefix('settings')->group(function () {
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

            // Assessment Settings
            Route::get('/assessment-settings', function () {
                return view('settings.assessment-settings');
            })->name('settings.assessment-settings');
        });
    });

    // Support Center and Knowledge Base Routes
    Route::middleware(['auth:sanctum'])->prefix('support')->group(function () {
        Route::get('/tickets', App\Livewire\SupportTickets::class)->name('support.tickets');
        Route::get('/tickets/{ticketId}', App\Livewire\TicketDetail::class)->name('support.ticket.detail');
        Route::get('/knowledge-base', App\Livewire\KnowledgeBase::class)->name('support.knowledge-base');
        Route::get('/knowledgebase', App\Livewire\KnowledgeBase::class)->name('support.knowledgebase'); // Alias for backward compatibility

        // KB Admin - Restricted to System, IT Manager, and Super Admin
        Route::middleware(['role:System|IT Manager|Super Admin'])->group(function () {
            Route::get('/kb-admin', App\Livewire\KnowledgeBaseAdmin::class)->name('support.kb-admin');
        });
    });

    // Office Management - Restricted to System, IT Manager, and Super Admin
    Route::middleware(['auth:sanctum', 'role:System|IT Manager|Super Admin'])->group(function () {
        Route::get('/offices', function () {
            return view('offices');
        })->name('offices');
    });

    // Legacy support center redirect (keep for backward compatibility)
    Route::middleware(['auth:sanctum'])->get('/support-center', function () {
        return redirect()->route('support.tickets');
    })->name('supportcenter');

    Route::get('/staffmail', function () {
        return redirect()->away('https://mail.hostinger.com/');
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
    })->middleware('role:Super Admin|System')->name('exam.response.tracker');

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
        // Election Results Archive
        Route::get('/elections/results-archive', function () {
            return view('elections.results-archive');
        })->name('elections.results.archive');
    });

    // Student Voting Routes
    Route::prefix('voting')->group(function () {
        Route::get('/{election}/verify', \App\Livewire\ElectionVoterVerification::class)->name('election.verify');
        // Route::get('/{election}/vote/{sessionId?}', \App\Livewire\ElectionVoting::class)->name('election.vote');
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
    Route::middleware(['auth:sanctum', 'role:Super Admin|Administrator|Finance Officer|Finance Manager'])->prefix('finance')->group(function () {
        Route::get('/billing', function () {
            return view('finance.billing');
        })->name('finance.billing');

        Route::get('/manager', function () {
            return view('test-view');
        })->name('manafter');

        Route::get('/exam-clearance', function () {
            return view('finance.exam-clearance');
        })->name('finance.exam.clearance');

        Route::get('/qr-scanner', \App\Livewire\Finance\ExamTicketScanner::class)->name('finance.exam.scanner');

        Route::get('/payments', \App\Livewire\Finance\FeePaymentManager::class)->name('finance.payments');

        Route::get('/reports', \App\Livewire\Finance\FinancialReportsManager::class)->name('finance.reports');

        Route::get('/fee-types', \App\Livewire\Finance\FeeTypesManager::class)->name('finance.fee.types');

        Route::get('/fee-structure', \App\Livewire\Finance\FeeStructureManager::class)->name('finance.fee.structure');

        Route::get('/exam-tickets-manager', function () {
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

        Route::get('/bill/edit/{billId}', function ($billId) {
            return view('finance.bill-edit', ['billId' => $billId]);
        })->name('finance.bill.edit');

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
    | Finance Course Registration Routes (System & Student Only)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'role:System|Student'])->prefix('finance')->group(function () {
        Route::get('/course-registration/{studentId?}', function ($studentId = null) {
            return view('finance.course-registration', ['studentId' => $studentId]);
        })->name('finance.course.registration');
    });

    /*
    |--------------------------------------------------------------------------
    | Course Registration Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'role:System|Student'])->group(function () {
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
    | Finance Officer Course Registration Approvals
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'role:Finance Officer|Super Admin|Administrator'])->group(function () {
        Route::get('/finance/course-registration-approvals', function () {
            return view('finance.course-registration-approvals');
        })->name('finance.course.registration.approvals');
    });

    /*
    |--------------------------------------------------------------------------
    | Support Center Routes
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'role:Super Admin|Administrator|System'])->prefix('settings')->group(function () {
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

        // API Settings Route
        Route::get('/api-settings', function () {
            return view('settings.api-settings');
        })->name('settings.api');
    });

    /*
    |--------------------------------------------------------------------------
    | Institution Branding Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'role:Super Admin|Administrator|System'])->prefix('admin')->group(function () {
        Route::get('/branding', [App\Http\Controllers\Admin\BrandingController::class, 'index'])->name('admin.branding.index');
        Route::post('/branding', [App\Http\Controllers\Admin\BrandingController::class, 'update'])->name('admin.branding.update');
        Route::get('/branding/preview', [App\Http\Controllers\Admin\BrandingController::class, 'preview'])->name('admin.branding.preview');
        Route::post('/branding/upload-logo', [App\Http\Controllers\Admin\BrandingController::class, 'uploadLogo'])->name('admin.branding.upload-logo');

        // Individual section updates
        Route::post('/branding/update-theme', [App\Http\Controllers\Admin\BrandingController::class, 'updateTheme'])->name('admin.branding.update-theme');
        Route::post('/branding/update-institution', [App\Http\Controllers\Admin\BrandingController::class, 'updateInstitution'])->name('admin.branding.update-institution');
        Route::post('/branding/update-urls', [App\Http\Controllers\Admin\BrandingController::class, 'updateUrls'])->name('admin.branding.update-urls');
        Route::post('/branding/update-colors', [App\Http\Controllers\Admin\BrandingController::class, 'updateColors'])->name('admin.branding.update-colors');
        Route::post('/branding/update-theme-settings', [App\Http\Controllers\Admin\BrandingController::class, 'updateThemeSettings'])->name('admin.branding.update-theme-settings');
        Route::post('/branding/update-student-id', [App\Http\Controllers\Admin\BrandingController::class, 'updateStudentIdSettings'])->name('admin.branding.update-student-id');

        // Course Assignment Management
        Route::get('/course-assignments', fn () => view('admin.course-assignments'))->name('admin.course-assignments');
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
            // Year Management (Livewire)
            Route::get('/years', \App\Livewire\Academics\YearManager::class)->name('years.index');
            Route::resource('classes', App\Http\Controllers\CollegeClassController::class);
            Route::post('/classes/filter', [App\Http\Controllers\CollegeClassController::class, 'filter'])->name('classes.filter');

            // Class Student Management
            Route::post('/classes/{class}/students/add', [App\Http\Controllers\CollegeClassController::class, 'addStudents'])->name('classes.students.add');
            Route::delete('/classes/{class}/students/{student}', [App\Http\Controllers\CollegeClassController::class, 'removeStudent'])->name('classes.students.remove');

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

            // Subjects/Courses Management
            Route::get('/courses', \App\Livewire\Academics\SubjectsManager::class)->name('courses.index');

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
        Route::get('/ai-sensei', \App\Livewire\Communication\AISenseiChat::class)->name('communication.ai-sensei');

        // SMS Contact Groups and Contacts Management
        Route::get('/contact-groups', \App\Livewire\Communication\ContactGroups::class)->name('communication.contact-groups');
        Route::get('/contacts/{groupId?}', \App\Livewire\Communication\Contacts::class)->name('communication.contacts');
        Route::get('/sms-logs', \App\Livewire\Communication\SmsLogs::class)->name('communication.sms-logs');

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

        // Offline Exam Scores Management
        Route::get('/exams/offline-scores', function () {
            return view('admin.exam.offline-scores');
        })->middleware('permission:view exams')->name('exams.offline-scores');

        // Transcript Generation
        Route::get('/transcripts', function () {
            return view('admin.transcripts.generation');
        })->middleware('permission:view exams')->name('transcripts.generation');

        // Exam Clearances Management
        Route::get('/exams/clearances', function () {
            return view('admin.exam.clearances');
        })->name('clearances.index');

        // Entry Tickets Management
        Route::get('/exams/tickets', function () {
            return view('admin.exam.tickets');
        })->name('tickets.index');
    });

    // Exam Extra Time Management (System, Lecturer, and Super Admin roles)
    Route::middleware(['auth:sanctum', 'role:System|Lecturer|Super Admin'])->group(function () {
        Route::get('/admin/exam-extra-time', function () {
            return view('exams.extra-time');
        })->name('admin.exam-extra-time');
    });

    /*
    |--------------------------------------------------------------------------
    | Notification Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth'])->prefix('notifications')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/get', [App\Http\Controllers\NotificationController::class, 'getNotifications']);
        Route::post('/{id}/mark-as-read', [App\Http\Controllers\NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [App\Http\Controllers\NotificationController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | Asset Management Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'role:Auditor|System|Super Admin'])->prefix('admin')->name('admin.')->group(function () {
        // Asset Management
        Route::resource('assets', App\Http\Controllers\AssetController::class);
        Route::resource('asset-categories', App\Http\Controllers\AssetCategoryController::class);

        // Asset Settings (Auditor, System and Super Admin)
        Route::get('asset-settings', [App\Http\Controllers\AssetSettingController::class, 'index'])->name('asset-settings.index');
        Route::post('asset-settings', [App\Http\Controllers\AssetSettingController::class, 'store'])->name('asset-settings.store');
        Route::patch('asset-settings/prefix', [App\Http\Controllers\AssetSettingController::class, 'updatePrefix'])->name('asset-settings.update-prefix');
        Route::delete('asset-settings/{assetSetting}', [App\Http\Controllers\AssetSettingController::class, 'destroy'])->name('asset-settings.destroy');
    });

});

/*
|--------------------------------------------------------------------------
| Public Election Routes
|--------------------------------------------------------------------------
|
| These routes are publicly accessible without authentication
| so students can vote without logging in to the system.
|
*/
Route::prefix('public/elections')->name('public.elections.')->group(function () {
    // View all active elections
    Route::get('/', \App\Livewire\PublicElections::class)->name('index');

    // View single election details
    Route::get('/{election}', \App\Livewire\PublicElectionDetails::class)->name('show');

    // Public verification and voting routes
    Route::get('/{election}/verify', \App\Livewire\ElectionVoterVerification::class)->name('verify');
    Route::get('/{election}/vote/{sessionId?}', \App\Livewire\ElectionVoting::class)->name('vote');
    Route::get('/{election}/thank-you/{sessionId?}', \App\Livewire\ElectionThankYou::class)->name('thank-you');
    Route::get('/{election}/thank-you/{sessionId?}', \App\Livewire\ElectionThankYou::class)->name('election.thank-you');

    Route::get('/{election}/expired', \App\Livewire\ElectionExpired::class)->name('expired');
});

// Staff Exam Preview (must be before generic student exam route to avoid conflicts)
Route::get('/exams/{exam}/preview', \App\Livewire\ExamPreview::class)
    ->middleware(['auth:sanctum', 'role:Lecturer|Academic Officer|Administrator|Super Admin|System'])
    ->name('exams.preview');

Route::get('/exams/{slug}/{student_id}', function ($slug, $student_id) {

    $exam = Exam::where('slug', $slug)->first();

    return view('frontend.exam', [
        'examPassword' => $exam->password,
        'student_id' => $student_id,
    ]);
})->where('slug', '(?!results).*')->name('exams'); // Exclude 'results' from matching

Route::get('/take-exam', function () {
    return view('frontend.take-exam');
})->name('take-exam');

Route::get('/extra-time', function () {
    return view('exams.extra-time');
})->name('extra-time');
