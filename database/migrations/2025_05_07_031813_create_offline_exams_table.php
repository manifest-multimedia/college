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
        Schema::create('offline_exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('date');
            $table->integer('duration')->comment('Duration in minutes');
            $table->string('status')->default('draft'); // draft, published, completed, canceled
            
            // Foreign key relationships - modified to avoid constraint issues
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('user_id')->comment('Created by');
            $table->unsignedBigInteger('type_id')->nullable();
            $table->unsignedBigInteger('proctor_id')->nullable()->comment('User who supervises the exam');
            
            // Specific fields for offline exams
            $table->string('venue')->nullable();
            $table->integer('clearance_threshold')->default(60)->comment('Percentage of fees required for clearance');
            $table->integer('passing_percentage')->default(50);
            
            $table->timestamps();
            
            // Define foreign keys separately to ensure proper order
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('type_id')->references('id')->on('exam_types')->nullOnDelete();
            $table->foreign('proctor_id')->references('id')->on('users')->nullOnDelete();
            
            // Add this last to ensure 'subjects' table exists and the column types match
            $table->foreign('course_id')->references('id')->on('subjects')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_exams');
    }
};
