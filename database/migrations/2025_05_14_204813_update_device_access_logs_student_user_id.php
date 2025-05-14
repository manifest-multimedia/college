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
        Schema::table('device_access_logs', function (Blueprint $table) {
            // Make student_user_id nullable
            $table->unsignedBigInteger('student_user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_access_logs', function (Blueprint $table) {
            // Revert back to non-nullable
            $table->unsignedBigInteger('student_user_id')->nullable(false)->change();
        });
    }
};
