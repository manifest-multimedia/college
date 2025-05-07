<?php

namespace App\Providers;

use App\Models\Exam;
use App\Models\FeePayment;
use App\Models\OfflineExam;
use App\Observers\ExamObserver;
use App\Observers\FeePaymentObserver;
use App\Observers\OfflineExamObserver;
use App\Services\Exams\ExamClearanceService;
use Illuminate\Support\ServiceProvider;

class ExamServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the ExamClearanceService in the service container
        $this->app->singleton(ExamClearanceService::class, function ($app) {
            return new ExamClearanceService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register model observers
        Exam::observe(ExamObserver::class);
        OfflineExam::observe(OfflineExamObserver::class);
        FeePayment::observe(FeePaymentObserver::class);
    }
}
