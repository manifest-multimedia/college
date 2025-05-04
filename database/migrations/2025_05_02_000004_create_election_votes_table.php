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
        if (!Schema::hasTable('election_votes')) {
            Schema::create('election_votes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('election_id')->constrained()->onDelete('cascade');
                $table->foreignId('election_position_id')->constrained()->onDelete('cascade');
                $table->foreignId('election_candidate_id')->constrained()->onDelete('cascade');
                $table->string('student_id'); // Student ID from the existing system
                $table->string('session_id')->nullable(); // For vote verification
                $table->string('ip_address')->nullable(); // IP address of the voter
                $table->text('user_agent')->nullable(); // Browser/device information
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();

                // A student can only vote once per position in an election
                // Using a shorter custom index name to avoid MySQL identifier length limit
                $table->unique(['election_id', 'election_position_id', 'student_id'], 'ev_election_position_student_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('election_votes');
    }
};