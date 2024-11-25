<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('exams', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['course_id']);

            // Add the new foreign key constraint
            $table->foreign('course_id')
                ->references('id')
                ->on('subjects')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('exams', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['course_id']);

            // Restore the original foreign key constraint
            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->onDelete('cascade');
        });
    }
};
