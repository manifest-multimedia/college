<?php

namespace App\Providers;

use App\Services\Communication\Chat\ChatServiceInterface;
use App\Services\Communication\Chat\OpenAI\OpenAIFilesService;
use App\Services\Communication\Chat\OpenAIChatService;
use App\Services\Communication\Email\EmailServiceInterface;
use App\Services\Communication\Email\LaravelMailService;
use App\Services\Communication\SMS\NaloSmsService;
use App\Services\Communication\SMS\SmsServiceInterface;
use App\Services\Communication\SMS\TwilioSmsService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class CommunicationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register SMS services
        $this->app->bind(SmsServiceInterface::class, function ($app) {
            // Get the default SMS provider from configuration
            $defaultProvider = Config::get('communication.default_sms_provider', 'twilio');

            return match ($defaultProvider) {
                'manifest-digital', 'nalo' => new NaloSmsService, // Allow both names for backward compatibility
                default => new TwilioSmsService,
            };
        });

        // Register Email services
        $this->app->bind(EmailServiceInterface::class, function ($app) {
            // You can switch the implementation based on configuration
            return new LaravelMailService;
        });

        // Register OpenAIFilesService
        $this->app->singleton(OpenAIFilesService::class, function ($app) {
            return new OpenAIFilesService;
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
