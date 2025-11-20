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
        Schema::table('semesters', function (Blueprint $table) {
            // Add academic_year_id if it doesn't exist
            if (! Schema::hasColumn('semesters', 'academic_year_id')) {
                $table->unsignedInteger('academic_year_id')->nullable();
                $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('set null');
            }

            // If year_id exists but not academic_year_id, copy data from year_id
            if (Schema::hasColumn('semesters', 'year_id') && Schema::hasColumn('semesters', 'academic_year_id')) {
                // This will be handled by the MigrateYearData command
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            // Only drop if we explicitly added it
            if (Schema::hasColumn('semesters', 'academic_year_id')) {
                $table->dropForeign(['academic_year_id']);
                $table->dropColumn('academic_year_id');
            }
        });
    }
};
