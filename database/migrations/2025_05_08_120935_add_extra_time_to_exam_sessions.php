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
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('exam_sessions', 'extra_time_minutes')) {
                $table->integer('extra_time_minutes')->default(0)->after('completed_at');
            }
            
            if (!Schema::hasColumn('exam_sessions', 'extra_time_added_by')) {
                $table->bigInteger('extra_time_added_by')->nullable()->after('extra_time_minutes');
            }
            
            if (!Schema::hasColumn('exam_sessions', 'extra_time_added_at')) {
                $table->timestamp('extra_time_added_at')->nullable()->after('extra_time_added_by');
            }
            
            // Note: We're removing the foreign key constraint to avoid compatibility issues
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            // Drop columns if they exist
            $columns = ['extra_time_minutes', 'extra_time_added_by', 'extra_time_added_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('exam_sessions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
