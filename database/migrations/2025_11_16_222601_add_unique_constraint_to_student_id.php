<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // First, let's handle any existing duplicates by appending a suffix
            DB::statement("
                UPDATE students s1 
                JOIN (
                    SELECT student_id, MIN(id) as min_id 
                    FROM students 
                    WHERE student_id IS NOT NULL AND student_id != ''
                    GROUP BY student_id 
                    HAVING COUNT(*) > 1
                ) s2 ON s1.student_id = s2.student_id 
                SET s1.student_id = CONCAT(s1.student_id, '_', s1.id)
                WHERE s1.id != s2.min_id
            ");
            
            // Add unique constraint on student_id (only for non-null values)
            $table->unique('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['student_id']);
        });
    }
};
