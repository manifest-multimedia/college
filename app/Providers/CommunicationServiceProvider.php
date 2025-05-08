<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Communication\SMS\SmsServiceInterface;
use App\Services\Communication\SMS\TwilioSmsService;
use App\Services\Communication\Email\EmailServiceInterface;
use App\Services\Communication\Email\LaravelMailService;
use App\Services\Communication\Chat\ChatServiceInterface;
use App\Services\Communication\Chat\OpenAIChatService;
use App\Services\Communication\Chat\OpenAI\OpenAIFilesService;

class CommunicationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register SMS services
        $this->app->bind(SmsServiceInterface::class, function ($app) {
            // You can switch the implementation based on configuration
            return new TwilioSmsService();
        });

        // Register Email services
        $this->app->bind(EmailServiceInterface::class, function ($app) {
            // You can switch the implementation based on configuration
            return new LaravelMailService();
        });

        // Register OpenAIFilesService
        $this->app->singleton(OpenAIFilesService::class, function ($app) {
            return new OpenAIFilesService();
        });

        // Register Chat services
        $this->app->bind(ChatServiceInterface::class, function ($app) {
            // You can switch the implementation based on configuration
            return new OpenAIChatService($app->make(OpenAIFilesService::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}