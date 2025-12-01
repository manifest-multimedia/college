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
            // Drop existing foreign keys
            $table->dropForeign(['exam_session_id']);
            $table->dropForeign(['student_user_id']);
            $table->dropForeign(['exam_id']);
            $table->dropForeign(['student_id']);
            
            // Recreate foreign keys with cascade delete
            $table->foreign('exam_session_id')
                ->references('id')
                ->on('exam_sessions')
                ->onDelete('cascade');
            
            $table->foreign('student_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            $table->foreign('exam_id')
                ->references('id')
                ->on('exams')
                ->onDelete('cascade');
            
            $table->foreign('student_id')
                ->references('id')
                ->on('students')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_access_logs', function (Blueprint $table) {
            // Drop cascade foreign keys
            $table->dropForeign(['exam_session_id']);
            $table->dropForeign(['student_user_id']);
            $table->dropForeign(['exam_id']);
            $table->dropForeign(['student_id']);
            
            // Recreate without cascade
            $table->foreign('exam_session_id')
                ->references('id')
                ->on('exam_sessions');
            
            $table->foreign('student_user_id')
                ->references('id')
                ->on('users');
            
            $table->foreign('exam_id')
                ->references('id')
                ->on('exams');
            
            $table->foreign('student_id')
                ->references('id')
                ->on('students');
        });
    }
};
