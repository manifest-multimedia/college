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
        if (!Schema::hasTable('election_candidates')) {
            Schema::create('election_candidates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('election_id')->constrained()->onDelete('cascade');
                $table->foreignId('election_position_id')->constrained('election_positions')->onDelete('cascade');
                // Create foreign key to students table the student's table id uses increments
                $table->unsignedInteger('student_id')->nullable();
                $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                $table->text('manifesto')->nullable();
                $table->string('photo')->nullable();
                $table->boolean('is_approved')->default(false);
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