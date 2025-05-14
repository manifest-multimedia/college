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
        
// Drop the table if it exists
        Schema::dropIfExists('device_access_logs');
        // Create the device_access_logs table
        Schema::create('device_access_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('exam_session_id');
            $table->unsignedInteger('student_id');
            $table->unsignedInteger('exam_id');
            $table->string('device_info');
            $table->string('session_token');
            $table->string('ip_address');
            $table->boolean('is_conflict')->default(false);
            $table->timestamp('access_time');
            $table->timestamps();
            
            // Define foreign keys
            $table->foreign('exam_session_id')->references('id')->on('exam_sessions');
            $table->foreign('student_id')->references('id')->on('users');
            $table->foreign('exam_id')->references('id')->on('exams');
            
            // Add index for faster lookups
            $table->index(['exam_session_id', 'access_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_access_logs');
    }
};
