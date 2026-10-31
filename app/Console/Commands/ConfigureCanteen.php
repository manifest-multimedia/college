<?php

namespace App\Console\Commands;

use App\Services\Canteen\CanteenPolicy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ConfigureCanteen extends Command
{
    protected $signature = 'canteen:configure {--academic-year=} {--semester=} {--payment-threshold=}';

    protected $description = 'Save this institution\'s approved canteen period and payment rule';

    public function handle(CanteenPolicy $policy): int
    {
        try {
            DB::transaction(function () use ($policy) {
                if ($this->option('academic-year') !== null || $this->option('semester') !== null) {
                    if (! ctype_digit((string) $this->option('semester'))) {
                        throw new InvalidArgumentException('Both a year and a numeric semester are required.');
                    }
                    $policy->setPeriod((string) $this->option('academic-year'), (int) $this->option('semester'));
                }
                if ($this->option('payment-threshold') !== null) {
                    if (! is_numeric($this->option('payment-threshold'))) {
                        throw new InvalidArgumentException('Payment threshold must be numeric.');
                    }
                    $policy->setPaymentThreshold((float) $this->option('payment-threshold'));
                }
            });
        } catch (InvalidArgumentException $error) {
            $this->error($error->getMessage());

            return self::FAILURE;
        }
        $this->line(json_encode(['period' => $policy->period(), 'minimum_payment_percentage' => $policy->paymentThreshold()]));

        return self::SUCCESS;
    }
}
