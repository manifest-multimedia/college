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
        if (! Schema::hasTable('election_candidates')) {
            Schema::create('election_candidates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('election_id')->constrained()->onDelete('cascade');
                $table->foreignId('election_position_id')->constrained('election_positions')->onDelete('cascade');
                // Create foreign key to students table the student's table id uses increments
                $table->unsignedInteger('student_id')->nullable();
                $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                $table->string('name')->nullable(); // Candidate name (will be derived from student if linked)
                $table->text('bio')->nullable(); // Candidate biography/description
                $table->text('manifesto')->nullable(); // Text version of the manifesto
                $table->string('manifesto_path')->nullable(); // File path for uploaded manifesto PDF
                $table->string('photo')->nullable(); // Legacy field for backward compatibility
                $table->string('image_path')->nullable(); // File path for uploaded image
                $table->boolean('is_approved')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('display_order')->default(0);
                $table->timestamps();

                // Ensure a student can only be a candidate once per position
                $table->unique(['election_position_id', 'student_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('election_candidates');
    }
};
