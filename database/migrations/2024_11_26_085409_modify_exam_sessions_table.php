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
        Schema::table('exam_sessions', function (Blueprint $table) {
            // Make sure the 'student_id' is unsigned if it isn't already
            $table->unsignedBigInteger('student_id')->change(); // Change type if necessary

            // Drop the existing foreign key constraint (if it references 'users.id')
            $table->dropForeign(['student_id']);

            // Change the foreign key to reference the 'students.id' instead of 'users.id'
            $table->foreign('student_id')
                ->references('id')->on('students')
                ->onDelete('cascade'); // Ensures that if a student is deleted, their exam session is also deleted
        });
    }

    public function down()
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            // Reverse the foreign key change if rolling back the migration
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }
};
