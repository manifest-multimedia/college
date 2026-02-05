<?php

namespace App\Console\Commands;

use App\Models\StudentFeeBill;
use Illuminate\Console\Command;

class RecalculateBillStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:recalculate-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate payment status for all student fee bills based on actual payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to recalculate bill payment statuses...');

        $bills = StudentFeeBill::all();
        $updated = 0;
        $unchanged = 0;

        $progressBar = $this->output->createProgressBar($bills->count());
        $progressBar->start();

        foreach ($bills as $bill) {
            $oldStatus = $bill->status;
            $bill->recalculatePaymentStatus();

            if ($oldStatus !== $bill->status) {
                $updated++;
            } else {
                $unchanged++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info('Recalculation complete!');
        $this->table(
            ['Status', 'Count'],
            [
                ['Updated', $updated],
                ['Unchanged', $unchanged],
                ['Total', $bills->count()],
            ]
        );

        return Command::SUCCESS;
    }
}
