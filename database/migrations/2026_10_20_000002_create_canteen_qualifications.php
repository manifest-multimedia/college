<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canteen_qualification_imports', function (Blueprint $table) {
            $table->id();
            $table->string('institution_prefix', 64);
            // Preserve confirmed scope without inventing academic dates.
            $table->string('academic_year', 32);
            $table->unsignedSmallInteger('semester');
            $table->string('file_name');
            $table->string('file_sha256', 64);
            $table->string('class_label');
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('imported_at');
            $table->timestamp('revoked_at')->nullable();
            $table->text('revocation_reason')->nullable();
            $table->unique(['institution_prefix', 'academic_year', 'semester', 'file_sha256'], 'canteen_import_identity');
        });
        Schema::create('canteen_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('canteen_qualification_imports')->restrictOnDelete();
            $table->unsignedInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students')->restrictOnDelete();
            $table->string('index_number', 64);
            $table->unsignedInteger('source_row');
            $table->boolean('qualified');
            $table->json('source_record');
            $table->unique(['import_id', 'index_number']);
            $table->index(['student_id', 'id']);
        });
        Schema::table('canteen_verifications', function (Blueprint $table) {
            $table->json('decision_context')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('canteen_verifications', fn (Blueprint $table) => $table->dropColumn('decision_context'));
        Schema::dropIfExists('canteen_qualifications');
        Schema::dropIfExists('canteen_qualification_imports');
    }
};
