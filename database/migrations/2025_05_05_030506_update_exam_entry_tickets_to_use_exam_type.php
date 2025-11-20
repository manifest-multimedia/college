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
        Schema::table('exam_entry_tickets', function (Blueprint $table) {
            // Drop foreign key constraint if it exists
            if (Schema::hasColumn('exam_entry_tickets', 'exam_id')) {
                $table->dropForeign(['exam_id']);
            }

            // Drop the exam_id column
            $table->dropColumn('exam_id');

            // Add exam_type_id column with foreign key constraint
            $table->foreignId('exam_type_id')->after('student_id')
                ->constrained('exam_types')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_entry_tickets', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['exam_type_id']);

            // Drop the exam_type_id column
            $table->dropColumn('exam_type_id');

            // Add back the exam_id column
            $table->foreignId('exam_id')->after('student_id')
                ->nullable()
                ->constrained('exams')
                ->onDelete('set null');
        });
    }
};
