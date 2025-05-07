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
        Schema::create('memo_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('memo_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('action_type', ['created', 'viewed', 'forwarded', 'approved', 'rejected', 'commented', 'completed', 'procured', 'delivered', 'audited']);
            $table->text('comment')->nullable();
            $table->foreignId('forwarded_to_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('forwarded_to_department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memo_actions');
    }
};
