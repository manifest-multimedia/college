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
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('recipient');
            $table->text('message');
            $table->string('provider')->nullable(); // e.g., Twilio, Nexmo
            $table->enum('type', ['single', 'bulk', 'group'])->default('single');
            $table->string('group_id')->nullable();
            $table->string('status')->default('pending'); // pending, sent, failed, delivered
            $table->text('response_data')->nullable(); // Store provider response
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
