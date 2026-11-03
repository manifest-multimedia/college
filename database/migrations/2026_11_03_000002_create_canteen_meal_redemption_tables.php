<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canteen_meal_policies', function (Blueprint $table) {
            $table->string('institution_prefix', 64)->primary();
            $table->uuid('version');
            $table->json('configuration');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('canteen_meal_policy_changes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('institution_prefix', 64)->index();
            $table->uuid('version');
            $table->uuid('previous_version')->nullable();
            $table->json('previous_configuration')->nullable();
            $table->json('configuration');
            $table->unsignedBigInteger('changed_by');
            $table->timestamp('created_at');
        });
        Schema::create('canteen_meal_devices', function (Blueprint $table) {
            $table->uuid('device_id')->primary();
            $table->string('label', 80);
            $table->char('key_hash', 64);
            $table->unsignedBigInteger('registered_by');
            $table->timestamps();
        });
        Schema::create('canteen_meal_assignments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('institution_prefix', 64);
            $table->string('group_key', 80);
            $table->uuid('device_id');
            $table->timestamps();
            $table->unique(['institution_prefix', 'group_key'], 'canteen_unique_serving_group');
        });
        Schema::create('canteen_meal_assignment_changes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('institution_prefix', 64);
            $table->json('assignments');
            $table->unsignedBigInteger('changed_by');
            $table->timestamp('created_at');
        });
        Schema::create('canteen_meal_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('institution_prefix', 64);
            $table->date('service_date');
            $table->json('policy');
            $table->json('students');
            $table->timestamps();
            $table->unique(['institution_prefix', 'service_date'], 'canteen_daily_serving_plan');
        });
        Schema::create('canteen_meal_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('institution_prefix', 64);
            $table->string('device_id', 64);
            $table->unsignedBigInteger('attendant_user_id');
            $table->date('service_date');
            $table->uuid('policy_version');
            $table->json('payload');
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->index(['institution_prefix', 'service_date']);
        });
        Schema::create('canteen_meal_redemptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('institution_prefix', 64);
            $table->string('index_number', 64);
            $table->unsignedInteger('student_id')->nullable();
            $table->date('service_date');
            $table->string('meal', 16);
            $table->uuid('client_request_id')->unique();
            $table->string('device_id', 64);
            $table->unsignedBigInteger('attendant_user_id');
            $table->boolean('is_offline')->default(false);
            $table->uuid('snapshot_id')->nullable();
            $table->timestamp('redeemed_at');
            $table->timestamps();
            // The final authority even when independent scanners race.
            $table->unique(['institution_prefix', 'index_number', 'service_date', 'meal'], 'canteen_one_meal_per_student');
        });
        Schema::create('canteen_meal_sync_rejections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('client_request_id');
            $table->uuid('device_id');
            $table->string('institution_prefix', 64);
            $table->json('event');
            $table->text('reason');
            $table->unsignedBigInteger('synced_by');
            $table->timestamp('created_at');
            $table->unique(['client_request_id', 'device_id'], 'canteen_rejected_sync_event');
        });
        Schema::create('canteen_meal_attempts', function (Blueprint $table) {
            $table->uuid('client_request_id')->primary();
            $table->string('institution_prefix', 64);
            $table->string('index_number', 64);
            $table->string('device_id', 64);
            $table->unsignedBigInteger('attendant_user_id');
            $table->char('request_fingerprint', 64);
            $table->string('status', 32);
            $table->unsignedBigInteger('synced_by')->nullable();
            $table->boolean('is_offline')->default(false);
            $table->json('response');
            $table->json('original_response')->nullable();
            $table->timestamps();
            $table->index(['institution_prefix', 'status', 'created_at'], 'canteen_meal_attempt_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canteen_meal_attempts');
        Schema::dropIfExists('canteen_meal_sync_rejections');
        Schema::dropIfExists('canteen_meal_redemptions');
        Schema::dropIfExists('canteen_meal_snapshots');
        Schema::dropIfExists('canteen_meal_plans');
        Schema::dropIfExists('canteen_meal_assignment_changes');
        Schema::dropIfExists('canteen_meal_assignments');
        Schema::dropIfExists('canteen_meal_devices');
        Schema::dropIfExists('canteen_meal_policy_changes');
        Schema::dropIfExists('canteen_meal_policies');
    }
};
