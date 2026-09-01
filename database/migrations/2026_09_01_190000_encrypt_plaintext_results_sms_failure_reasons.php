<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Throwable;

return new class extends Migration
{
    /**
     * Repair failure messages written by the original queue failure handler.
     * Those writes bypassed the model's encrypted cast.
     */
    public function up(): void
    {
        DB::table('results_sms_upload_batches')
            ->whereNotNull('failure_reason')
            ->orderBy('id')
            ->each(function (object $batch): void {
                try {
                    Crypt::decryptString($batch->failure_reason);
                } catch (Throwable) {
                    DB::table('results_sms_upload_batches')
                        ->where('id', $batch->id)
                        ->update(['failure_reason' => Crypt::encryptString($batch->failure_reason)]);
                }
            });
    }

    public function down(): void
    {
        // Encrypted audit information must not be downgraded to plaintext.
    }
};
