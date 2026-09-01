<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results_sms_upload_batches', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->foreignId('confirmed_by')->nullable()->constrained('users');
            $table->text('original_filename');
            $table->string('stored_path');
            $table->string('file_hash', 64);
            $table->string('file_extension', 8);
            $table->string('status', 32)->default('validating')->index();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('ready_rows')->default(0);
            $table->unsignedInteger('sent_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->unsignedInteger('skipped_rows')->default(0);
            $table->unsignedInteger('pending_review_rows')->default(0);
            $table->unsignedInteger('missing_student_rows')->default(0);
            $table->unsignedInteger('missing_number_rows')->default(0);
            $table->unsignedInteger('duplicate_id_rows')->default(0);
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('results_sms_upload_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('results_sms_upload_batches')->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            // students.id is an unsigned integer in this long-lived schema.
            $table->unsignedInteger('student_record_id')->nullable();
            $table->foreign('student_record_id')->references('id')->on('students')->nullOnDelete();
            $table->text('student_id');
            $table->char('student_id_hash', 64)->index();
            $table->longText('message');
            $table->char('message_hash', 64)->index();
            $table->text('uploaded_status')->nullable();
            $table->string('status', 32)->default('pending_review')->index();
            $table->string('safe_reason')->nullable();
            $table->string('masked_recipient', 32)->nullable();
            $table->longText('normalized_recipient')->nullable();
            $table->longText('provider_response')->nullable();
            $table->unsignedTinyInteger('attempt_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'row_number']);
            $table->index(['batch_id', 'status']);
            $table->index(['batch_id', 'student_id_hash', 'message_hash'], 'results_sms_idempotency_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results_sms_upload_rows');
        Schema::dropIfExists('results_sms_upload_batches');
    }
};
