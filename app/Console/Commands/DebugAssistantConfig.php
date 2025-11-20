<?php

namespace App\Console\Commands;

use App\Services\Communication\Chat\OpenAI\OpenAIAssistantsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class DebugAssistantConfig extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ai:debug-assistant {--assistant-id=}';

    /**
     * The console command description.
     */
    protected $description = 'Debug assistant configuration and tools';

    protected $assistantService;

    /**
     * Create a new command instance.
     */
    public function __construct(OpenAIAssistantsService $assistantService)
    {
        parent::__construct();
        $this->assistantService = $assistantService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $assistantId = $this->option('assistant-id') ?? Config::get('services.openai.assistant_id');

        if (! $assistantId) {
            $this->error('No assistant ID provided. Use --assistant-id option or set OPENAI_ASSISTANT_ID in your .env file.');

            return 1;
        }

        $this->info("Debugging assistant configuration for: {$assistantId}");
        $this->newLine();

        try {
            // Retrieve assistant details
            $response = $this->assistantService->retrieveAssistant($assistantId);

            if (! $response['success']) {
                $this->error('❌ Failed to retrieve assistant: '.($response['message'] ?? 'Unknown error'));

                return 1;
            }

            $assistant = $response['data'];

            $this->info('📋 Assistant Configuration:');
            $this->info('Name: '.($assistant['name'] ?? 'N/A'));
            $this->info('Model: '.($assistant['model'] ?? 'N/A'));
            $this->info('Created: '.date('Y-m-d H:i:s', $assistant['created_at'] ?? 0));

            $this->newLine();
            $this->info('🔧 Tools Configuration:');

            if (! empty($assistant['tools'])) {
                $this->info('Found '.count($assistant['tools']).' tools:');
                foreach ($assistant['tools'] as $index => $tool) {
                    $this->info("  {$index}. Type: ".$tool['type']);
                    if ($tool['type'] === 'function') {
                        $this->info('     Function: '.($tool['function']['name'] ?? 'N/A'));
                        $this->info('     Description: '.($tool['function']['description'] ?? 'N/A'));
                    }
                }
            } else {
                $this->warn('⚠️ No tools configured for this assistant');
            }

            $this->newLine();
            $this->info('📝 Instructions (first 200 chars):');
            $instructions = $assistant['instructions'] ?? 'No instructions set';
            $this->info(substr($instructions, 0, 200).(strlen($instructions) > 200 ? '...' : ''));

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Exception occurred: '.$e->getMessage());

            return 1;
        }
    }
}
