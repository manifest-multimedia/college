<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_identifications', function (Blueprint $table) {
            $table->id();
            // students.id is an unsigned integer in the established schema.
            $table->unsignedInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->string('identification_type', 40);
            $table->text('identifier');
            $table->char('identifier_hash', 64);
            $table->string('issuing_country', 2)->nullable();
            $table->string('issuing_authority')->nullable();
            $table->date('expires_on')->nullable();
            $table->string('verification_status', 20)->default('unverified');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('image_path')->nullable();
            $table->string('image_mime_type', 100)->nullable();
            $table->unsignedInteger('image_size')->nullable();
            $table->timestamps();

            $table->unique(['identification_type', 'identifier_hash'], 'student_identification_unique_identifier');
            $table->index(['student_id', 'identification_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_identifications');
    }
};
