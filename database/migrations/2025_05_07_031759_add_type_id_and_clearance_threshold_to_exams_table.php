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
        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('type_id')->nullable()->after('type')
                ->comment('Foreign key to exam_types table')
                ->constrained('exam_types')->nullOnDelete();

            $table->integer('clearance_threshold')->default(60)->after('passing_percentage')
                ->comment('Percentage of fees required for clearance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
            $table->dropColumn(['type_id', 'clearance_threshold']);
        });
    }
};
