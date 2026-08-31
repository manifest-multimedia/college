<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A semester is an academic-period instance, not a global definition.
     * This allows "Semester 1" to exist once in every academic year while
     * preserving all existing semester IDs and linked historical records.
     */
    public function up(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->unsignedSmallInteger('sequence')->nullable()->after('academic_year_id');
        });

        DB::table('semesters')
            ->whereNotNull('academic_year_id')
            ->orderBy('academic_year_id')
            ->orderBy('start_date')
            ->orderBy('id')
            ->get(['id', 'academic_year_id'])
            ->groupBy('academic_year_id')
            ->each(function ($semesters): void {
                foreach ($semesters->values() as $index => $semester) {
                    DB::table('semesters')->where('id', $semester->id)->update(['sequence' => $index + 1]);
                }
            });

        Schema::table('semesters', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->unique(['academic_year_id', 'name'], 'semesters_year_name_unique');
            $table->unique(['academic_year_id', 'sequence'], 'semesters_year_sequence_unique');
        });
    }

    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropUnique('semesters_year_name_unique');
            $table->dropUnique('semesters_year_sequence_unique');
            $table->unique('name');
            $table->dropColumn('sequence');
        });
    }
};
