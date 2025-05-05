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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('recipient');
            $table->string('subject');
            $table->text('message');
            $table->string('cc')->nullable();
            $table->string('bcc')->nullable();
            $table->string('template')->nullable(); // The template used (if applicable)
            $table->string('attachment')->nullable();
            $table->string('provider')->nullable(); // e.g., SMTP, Mailgun, SendGrid
            $table->enum('type', ['single', 'bulk', 'group'])->default('single');
            $table->string('group_id')->nullable();
            $table->string('status')->default('pending'); // pending, sent, failed, delivered, opened
            $table->text('response_data')->nullable(); // Store provider response
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
