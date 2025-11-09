<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add short_name column to college_classes table for Student ID generation
     */
    public function up(): void
    {
        Schema::table('college_classes', function (Blueprint $table) {
            $table->string('short_name', 10)->nullable()->after('name');
            $table->index('short_name'); // Add index for performance
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Remove short_name column from college_classes table
     */
    public function down(): void
    {
        Schema::table('college_classes', function (Blueprint $table) {
            $table->dropIndex(['short_name']);
            $table->dropColumn('short_name');
        });
    }
};
