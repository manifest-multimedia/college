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
        Schema::create('election_voting_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained()->onDelete('cascade');
            $table->string('student_id');
            $table->dateTime('started_at');
            $table->dateTime('expires_at');
            $table->dateTime('completed_at')->nullable();
            $table->boolean('vote_submitted')->default(false);
            $table->string('ip_address')->nullable();
            $table->string('session_id')->unique();
            $table->timestamps();
            
            // Ensure a student can only have one voting session per election
            $table->unique(['election_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('election_voting_sessions');
    }
};