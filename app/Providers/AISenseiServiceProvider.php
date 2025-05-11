<?php

namespace App\Providers;

use App\Services\Communication\Chat\AssistantsChatService;
use App\Services\Communication\Chat\ChatServiceInterface;
use App\Services\Communication\Chat\Document\AISenseiDocumentService;
use App\Services\Communication\Chat\Document\DocumentUploadInterface;
use App\Services\Communication\Chat\OpenAI\OpenAIAssistantsService;
use App\Services\Communication\Chat\OpenAI\OpenAIFilesService;
use Illuminate\Support\ServiceProvider;

class AISenseiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the OpenAI Assistants Service
        $this->app->singleton(OpenAIAssistantsService::class, function ($app) {
            return new OpenAIAssistantsService();
        });
        
        // Register the OpenAI Files Service
        $this->app->singleton(OpenAIFilesService::class, function ($app) {
            return new OpenAIFilesService();
        });
        
        // Register the AISensei Document Service
        $this->app->singleton(DocumentUploadInterface::class, function ($app) {
            return new AISenseiDocumentService(
                $app->make(OpenAIAssistantsService::class)
            );
        });
        
        // Register the AI Assistants Chat Service
        $this->app->singleton(ChatServiceInterface::class, function ($app) {
            return new AssistantsChatService(
                $app->make(OpenAIAssistantsService::class),
                $app->make(OpenAIFilesService::class)
            );
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