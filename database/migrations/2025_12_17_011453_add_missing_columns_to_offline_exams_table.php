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
        Schema::table('offline_exams', function (Blueprint $table) {
            // Rename created_by to user_id for consistency
            // Check if column exists before renaming
            if (Schema::hasColumn('offline_exams', 'created_by')) {
                $table->renameColumn('created_by', 'user_id');
            }
            
            // Rename exam_date to date
            if (Schema::hasColumn('offline_exams', 'exam_date')) {
                $table->renameColumn('exam_date', 'date');
            }
            
            // Add missing columns
            // Add if column does not exist
            if (!Schema::hasColumn('offline_exams', 'duration')) {
            $table->integer('duration')->nullable()->after('date'); // Duration in minutes
            }
            if (!Schema::hasColumn('offline_exams', 'status')) {
            $table->string('status')->default('draft')->after('duration'); // draft, published, completed, canceled
            }
            if(!Schema::hasColumn('offline_exams', 'proctor_id')) {
            $table->unsignedBigInteger('proctor_id')->nullable()->after('user_id'); // Invigilator
            }
            if (!Schema::hasColumn('offline_exams', 'proctor_id')) {
                $table->foreign('proctor_id')->references('id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('offline_exams', 'venue')) {
            $table->string('venue')->nullable()->after('proctor_id');
            }
            if (!Schema::hasColumn('offline_exams', 'clearance_threshold')) {
            $table->integer('clearance_threshold')->default(60)->after('venue'); // Percentage required for clearance
            }
            if (!Schema::hasColumn('offline_exams', 'passing_percentage')) {
            $table->integer('passing_percentage')->default(50)->after('clearance_threshold');
            }
            
            // Drop total_marks column as it's not used in the current implementation
            // Check if column exists before dropping
            if (Schema::hasColumn('offline_exams', 'total_marks')) {
            $table->dropColumn('total_marks');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offline_exams', function (Blueprint $table) {
            // Reverse the changes
            $table->renameColumn('user_id', 'created_by');
            $table->renameColumn('date', 'exam_date');
            $table->dropForeign(['proctor_id']);
            $table->dropColumn(['duration', 'status', 'proctor_id', 'venue', 'clearance_threshold', 'passing_percentage']);
            $table->decimal('total_marks', 8, 2)->after('created_by');
        });
    }
};
