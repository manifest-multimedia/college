<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('results_sms_upload_batches', function (Blueprint $table): void {
            // The encrypted source is kept with the batch so queue workers do
            // not rely on release-local storage remaining available.
            $table->longText('encrypted_upload_contents')->nullable()->after('stored_path');
        });
    }

    public function down(): void
    {
        Schema::table('results_sms_upload_batches', function (Blueprint $table): void {
            $table->dropColumn('encrypted_upload_contents');
        });
    }
};
