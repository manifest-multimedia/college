<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tutor_evaluation_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('tutor_evaluation_form_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('tutor_evaluation_forms')->restrictOnDelete();
            $table->unsignedInteger('version');
            $table->json('schema');
            $table->timestamp('published_at');
            $table->timestamps();
            $table->unique(['form_id', 'version']);
        });
        Schema::create('tutor_evaluation_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters')->restrictOnDelete();
            $table->foreignId('form_version_id')->constrained('tutor_evaluation_form_versions')->restrictOnDelete();
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_required')->default(false); // Reserved for a future access-policy integration.
            $table->unsignedSmallInteger('report_minimum')->default(5);
            $table->timestamps();
        });
        Schema::create('tutor_evaluation_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('tutor_evaluation_campaigns')->restrictOnDelete();
            $table->unsignedInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students')->restrictOnDelete();
            $table->unsignedBigInteger('lecturer_id');
            $table->foreign('lecturer_id')->references('id')->on('users')->restrictOnDelete();
            $table->unsignedInteger('subject_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->restrictOnDelete();
            $table->timestamp('completed_at');
            $table->unique(['campaign_id', 'student_id', 'lecturer_id', 'subject_id'], 'tutor_eval_once_per_assignment');
        });
        Schema::create('tutor_evaluation_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('tutor_evaluation_campaigns')->restrictOnDelete();
            $table->unsignedBigInteger('lecturer_id');
            $table->foreign('lecturer_id')->references('id')->on('users')->restrictOnDelete();
            $table->unsignedInteger('subject_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->restrictOnDelete();
            $table->json('answers');
            // No respondent identifier, token, IP address, or submitted-at timestamp.
            $table->index(['campaign_id', 'lecturer_id', 'subject_id'], 'tutor_eval_report_lookup');
        });
    }

    public function down(): void
    {
        foreach (['tutor_evaluation_responses', 'tutor_evaluation_completions', 'tutor_evaluation_campaigns', 'tutor_evaluation_form_versions', 'tutor_evaluation_forms'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
