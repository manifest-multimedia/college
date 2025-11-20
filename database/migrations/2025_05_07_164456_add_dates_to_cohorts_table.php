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
        Schema::table('cohorts', function (Blueprint $table) {
            // Add date fields
            $table->date('start_date')->nullable()->after('academic_year');
            $table->date('end_date')->nullable()->after('start_date');

            // Make academic_year nullable (to maintain compatibility with existing records)
            $table->string('academic_year')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cohorts', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
            $table->string('academic_year')->nullable(false)->change();
        });
    }
};
