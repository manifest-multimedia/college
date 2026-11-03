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
        Schema::create('canteen_devices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('device_id', 64)->unique();
            $table->string('name')->nullable();
            $table->string('platform', 32)->nullable();
            $table->unsignedBigInteger('registered_by')->nullable();
            $table->foreign('registered_by')->references('id')->on('users')->onDelete('set null');
            $table->dateTime('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('canteen_verifications', function (Blueprint $table) {
            $table->increments('id');
            // Client-supplied idempotency key; offline captures and online scans both send one.
            $table->uuid('client_request_id')->nullable()->unique();
            $table->unsignedInteger('student_id')->nullable();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('set null');
            $table->unsignedBigInteger('attendant_user_id')->nullable();
            $table->foreign('attendant_user_id')->references('id')->on('users')->onDelete('set null');
            $table->string('device_id', 64)->nullable();
            $table->string('index_number', 64);
            $table->string('verification_method', 16);
            $table->string('decision', 24);
            $table->boolean('is_offline')->default(false);
            // Decision currently produced by the server, recorded when an offline capture syncs.
            $table->string('reconciled_decision', 24)->nullable();
            $table->json('student_snapshot')->nullable();
            $table->decimal('payment_percentage', 6, 2)->nullable();
            $table->dateTime('verified_at');
            $table->dateTime('synced_at')->nullable();
            $table->timestamps();

            $table->index(['decision', 'verified_at']);
            $table->index(['student_id', 'verified_at']);
            $table->index(['verified_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canteen_verifications');
        Schema::dropIfExists('canteen_devices');
    }
};
