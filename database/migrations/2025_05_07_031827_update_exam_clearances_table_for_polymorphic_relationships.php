<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exam_clearances', function (Blueprint $table) {
            // Add new polymorphic columns
            $table->string('clearable_type')->nullable()->after('exam_type_id');
            $table->unsignedBigInteger('clearable_id')->nullable()->after('clearable_type');

            // Add index for polymorphic relationship
            $table->index(['clearable_type', 'clearable_id']);

            // Update existing records to use the new polymorphic relationship
            // Default to App\Models\Exam as the clearable_type
            // This will be run in a separate update statement

            // Add status field (if it doesn't already exist)
            if (! Schema::hasColumn('exam_clearances', 'status')) {
                $table->string('status')->default('pending')->after('is_cleared')
                    ->comment('Cleared, Pending, Denied');
            }

            // Add comments field (if it doesn't already exist)
            if (! Schema::hasColumn('exam_clearances', 'comments')) {
                $table->text('comments')->nullable()->after('override_reason');
            }
        });

        // Add default values for existing records - assuming exam_type_id is related to exams
        DB::statement("UPDATE exam_clearances SET clearable_type = 'App\\Models\\Exam', clearable_id = exam_type_id WHERE clearable_type IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_clearances', function (Blueprint $table) {
            // Remove the new polymorphic columns
            $table->dropIndex(['clearable_type', 'clearable_id']);
            $table->dropColumn(['clearable_type', 'clearable_id']);

            // Remove status field if we added it
            if (Schema::hasColumn('exam_clearances', 'status')) {
                $table->dropColumn('status');
            }

            // Remove comments field if we added it
            if (Schema::hasColumn('exam_clearances', 'comments')) {
                $table->dropColumn('comments');
            }
        });
    }
};
