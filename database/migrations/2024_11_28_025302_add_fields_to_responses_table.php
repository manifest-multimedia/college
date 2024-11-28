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
        Schema::table('responses', function (Blueprint $table) {
            //    check if exists
            if (Schema::hasColumn('responses', 'option_id')) {
                return;
            } else {

                // Define option relationship
                $table->unsignedInteger('option_id')->nullable();
                $table->foreign('option_id')->references('id')->on('options');
            }
            // check if exists
            if (Schema::hasColumn('responses', 'student_id')) {
                return;
            } else {
                // Define student relationship
                $table->unsignedBigInteger('student_id')->nullable();
                $table->foreign('student_id')->references('id')->on('users');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('responses', function (Blueprint $table) {
            $table->dropForeign(['option_id']);
            $table->dropForeign(['student_id']);
        });
    }
};
