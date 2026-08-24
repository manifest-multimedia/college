<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_devices', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('label');
            $table->enum('device_type', ['mobile', 'laptop', 'desktop', 'tablet']);
            $table->string('status')->default('active');
            $table->string('token_hash', 64)->unique();
            $table->json('device_metadata')->nullable();
            $table->string('location')->nullable();
            $table->foreignId('registered_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('revocation_reason')->nullable();
            $table->timestamps();
            $table->index(['status', 'device_type']);
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->string('device_access_mode')->default('open')->after('status');
            $table->json('allowed_device_types')->nullable()->after('device_access_mode');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['device_access_mode', 'allowed_device_types']);
        });
        Schema::dropIfExists('exam_devices');
    }
};
