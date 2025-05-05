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
        Schema::create('recipient_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_list_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->json('metadata')->nullable(); // Additional data about the recipient
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Email and phone should not both be null
            $table->index(['email', 'phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipient_list_items');
    }
};
