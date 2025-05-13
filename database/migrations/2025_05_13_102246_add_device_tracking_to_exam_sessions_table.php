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
            $table->string('session_token')->nullable()->after('extra_time_added_at');
            $table->string('device_info')->nullable()->after('session_token');
            $table->timestamp('last_activity')->nullable()->after('device_info');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->dropColumn(['session_token', 'device_info', 'last_activity']);
        });
    }
};
