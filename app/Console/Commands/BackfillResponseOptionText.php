<?php

namespace App\Console\Commands;

use App\Models\Option;
use App\Models\Response;
use Illuminate\Console\Command;

class BackfillResponseOptionText extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'responses:backfill-option-text {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill existing responses with option text and option_id for data integrity protection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🔍 Running in DRY RUN mode - no changes will be made');
        }

        $this->info('🔎 Analyzing existing responses...');

        // Count responses needing backfill
        $responsesNeedingBackfill = Response::where(function ($query) {
            $query->whereNull('selected_option_text')
                ->orWhereNull('option_id');
        })->whereNotNull('selected_option')->count();

        if ($responsesNeedingBackfill === 0) {
            $this->info('✅ All responses already have option text and option_id populated!');

            return 0;
        }

        $this->info("📊 Found {$responsesNeedingBackfill} responses needing backfill");

        if (! $this->confirm('Do you want to proceed?', true)) {
            $this->info('Cancelled by user');

            return 1;
        }

        $this->info('🚀 Starting backfill process...');

        $progressBar = $this->output->createProgressBar($responsesNeedingBackfill);
        $progressBar->start();

        $updated = 0;
        $failed = 0;
        $skipped = 0;

        // Process responses in chunks for better performance
        Response::where(function ($query) {
            $query->whereNull('selected_option_text')
                ->orWhereNull('option_id');
        })->whereNotNull('selected_option')
            ->chunk(100, function ($responses) use ($isDryRun, $progressBar, &$updated, &$failed, &$skipped) {
                foreach ($responses as $response) {
                    try {
                        // Get the option based on selected_option field
                        $option = Option::find($response->selected_option);

                        if (! $option) {
                            // Option doesn't exist anymore - log this
                            $skipped++;
                            $this->newLine();
                            $this->warn("⚠️  Response ID {$response->id}: Option {$response->selected_option} not found - SKIPPED");
                        } else {
                            if (! $isDryRun) {
                                // Update the response with option text and option_id
                                $response->update([
                                    'option_id' => $option->id,
                                    'selected_option_text' => $option->option_text,
                                ]);
                            }
                            $updated++;
                        }
                    } catch (\Exception $e) {
                        $failed++;
                        $this->newLine();
                        $this->error("❌ Response ID {$response->id}: {$e->getMessage()}");
                    }

                    $progressBar->advance();
                }
            });

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('📈 Backfill Summary:');
        $this->table(
            ['Status', 'Count'],
            [
                ['✅ Updated', $updated],
                ['⚠️  Skipped (option not found)', $skipped],
                ['❌ Failed', $failed],
                ['📊 Total Processed', $updated + $skipped + $failed],
            ]
        );

        if ($isDryRun) {
            $this->warn('🔍 This was a DRY RUN - no changes were made to the database');
            $this->info('💡 Run without --dry-run to apply changes');
        } else {
            $this->info('✅ Backfill completed successfully!');

            if ($skipped > 0) {
                $this->warn("⚠️  {$skipped} responses were skipped because their options no longer exist");
                $this->info('💡 These responses will still have selected_option field but cannot be backfilled');
            }
        }

        return 0;
    }
}
