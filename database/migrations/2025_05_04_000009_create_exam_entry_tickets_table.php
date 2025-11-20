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

        Schema::create('exam_entry_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_clearance_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students');
            // Exam id uses increments id
            $table->unsignedInteger('exam_id')->nullable();
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
            $table->string('qr_code')->unique(); // Unique identifier for QR code
            $table->string('ticket_number')->unique();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable(); // User ID who verified the ticket
            $table->foreign('verified_by')->references('id')->on('users');
            $table->string('verification_location')->nullable();
            $table->string('verification_ip')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_entry_tickets');
    }
};
