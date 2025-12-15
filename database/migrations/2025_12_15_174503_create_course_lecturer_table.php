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
        Schema::create('course_lecturer', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Lecturer user ID
            $table->unsignedInteger('subject_id'); // Course/Subject ID
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');

            // Ensure a lecturer can't be assigned to the same course twice
            $table->unique(['user_id', 'subject_id']);

            // Indexes for faster lookups
            $table->index('user_id');
            $table->index('subject_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_lecturer');
    }
};
