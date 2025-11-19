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
        Schema::create('student_id_changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('student_id');
            $table->string('old_student_id');
            $table->string('new_student_id');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->enum('status', ['active', 'reverted', 'superseded'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['student_id', 'status']);
            $table->index('old_student_id');
            $table->index('new_student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_id_changes');
    }
};
