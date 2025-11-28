<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_sessions', 'device_mismatch_bypassed')) {
                $table->boolean('device_mismatch_bypassed')->default(false)->after('device_info');
            }
            if (!Schema::hasColumn('exam_sessions', 'device_mismatch_bypassed_at')) {
                $table->timestamp('device_mismatch_bypassed_at')->nullable()->after('device_mismatch_bypassed');
            }
            if (!Schema::hasColumn('exam_sessions', 'device_mismatch_bypassed_by')) {
                $table->string('device_mismatch_bypassed_by')->nullable()->after('device_mismatch_bypassed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('exam_sessions', 'device_mismatch_bypassed')) {
                $table->dropColumn('device_mismatch_bypassed');
            }
            if (Schema::hasColumn('exam_sessions', 'device_mismatch_bypassed_at')) {
                $table->dropColumn('device_mismatch_bypassed_at');
            }
            if (Schema::hasColumn('exam_sessions', 'device_mismatch_bypassed_by')) {
                $table->dropColumn('device_mismatch_bypassed_by');
            }
        });
    }
};
