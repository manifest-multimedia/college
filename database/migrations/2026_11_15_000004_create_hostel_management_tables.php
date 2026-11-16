<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hostel_settings', function (Blueprint $t) {
            $t->id(); $t->boolean('enabled')->default(false);
            $t->string('allocation_method')->default('staff');
            $t->string('allocation_unit')->default('bed');
            $t->string('period_type')->default('academic_year');
            $t->boolean('enforce_gender')->default(true);
            $t->boolean('require_check_in')->default(true);
            $t->boolean('allow_applications')->default(false);
            $t->boolean('require_payment')->default(false);
            $t->timestamps();
        });
        Schema::create('hostels', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('code')->unique();
            $t->string('location')->nullable(); $t->string('gender_policy')->default('mixed');
            $t->string('status')->default('active'); $t->text('description')->nullable(); $t->timestamps();
        });
        Schema::create('hostel_rooms', function (Blueprint $t) {
            $t->id(); $t->foreignId('hostel_id')->constrained('hostels')->restrictOnDelete();
            $t->string('room_number'); $t->string('floor')->nullable();
            $t->string('status')->default('active'); $t->timestamps();
            $t->unique(['hostel_id','room_number']);
        });
        Schema::create('hostel_beds', function (Blueprint $t) {
            $t->id(); $t->foreignId('hostel_room_id')->constrained('hostel_rooms')->restrictOnDelete();
            $t->string('bed_code'); $t->string('status')->default('active'); $t->timestamps();
            $t->unique(['hostel_room_id','bed_code']);
        });
        Schema::create('hostel_allocation_periods', function (Blueprint $t) {
            $t->id(); $t->string('name');
            $t->unsignedInteger('academic_year_id')->nullable(); $t->foreign('academic_year_id')->references('id')->on('academic_years')->nullOnDelete();
            $t->unsignedInteger('semester_id')->nullable(); $t->foreign('semester_id')->references('id')->on('semesters')->nullOnDelete();
            $t->date('starts_on'); $t->date('ends_on'); $t->string('status')->default('draft'); $t->timestamps();
        });
        Schema::create('hostel_allocations', function (Blueprint $t) {
            $t->id(); $t->unsignedInteger('student_id'); $t->foreign('student_id')->references('id')->on('students')->restrictOnDelete();
            $t->foreignId('hostel_bed_id')->constrained('hostel_beds')->restrictOnDelete();
            $t->foreignId('hostel_allocation_period_id')->constrained('hostel_allocation_periods')->restrictOnDelete();
            $t->string('status')->default('allocated');
            $t->timestamp('checked_in_at')->nullable(); $t->timestamp('checked_out_at')->nullable();
            $t->foreignId('allocated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->text('notes')->nullable(); $t->timestamps();
            $t->index(['hostel_bed_id','status']); $t->index(['student_id','status']);
        });
        Schema::create('hostel_allocation_events', function (Blueprint $t) {
            $t->id(); $t->foreignId('hostel_allocation_id')->constrained('hostel_allocations')->restrictOnDelete();
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('event'); $t->text('notes')->nullable(); $t->json('metadata')->nullable(); $t->timestamps();
        });
        Schema::create('hostel_applications', function (Blueprint $t) {
            $t->id(); $t->unsignedInteger('student_id'); $t->foreign('student_id')->references('id')->on('students')->restrictOnDelete();
            $t->foreignId('hostel_allocation_period_id')->constrained('hostel_allocation_periods')->restrictOnDelete();
            $t->foreignId('preferred_hostel_id')->nullable()->constrained('hostels')->nullOnDelete();
            $t->string('status')->default('pending'); $t->text('notes')->nullable();
            $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete(); $t->timestamp('reviewed_at')->nullable();
            $t->timestamps(); $t->unique(['student_id','hostel_allocation_period_id'], 'hostel_application_student_period_unique');
        });
    }
    public function down(): void
    {
        foreach (['hostel_applications','hostel_allocation_events','hostel_allocations','hostel_allocation_periods','hostel_beds','hostel_rooms','hostels','hostel_settings'] as $table) Schema::dropIfExists($table);
    }
};
