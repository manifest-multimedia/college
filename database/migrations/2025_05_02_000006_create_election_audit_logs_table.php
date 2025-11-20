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
        Schema::create('election_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_type')->nullable(); // admin, student
            $table->string('user_id')->nullable(); // admin_id or student_id
            $table->string('event'); // created, updated, deleted, voted, session_started, etc.
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('election_audit_logs');
    }
};
