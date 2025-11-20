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
        Schema::table('academic_years', function (Blueprint $table) {
            $table->year('year')->nullable()->after('name');
        });

        // Extract and populate the year from existing academic year names if possible
        // This assumes academic year names are in formats like "2024/2025" or similar
        $academicYears = DB::table('academic_years')->get();
        foreach ($academicYears as $academicYear) {
            // Try to extract the first year from the name
            if (preg_match('/(\d{4})/', $academicYear->name, $matches)) {
                $year = (int) $matches[1];
                DB::table('academic_years')
                    ->where('id', $academicYear->id)
                    ->update(['year' => $year]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropColumn('year');
        });
    }
};
