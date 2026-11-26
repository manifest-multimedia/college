<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_id_regeneration_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('cohort_id')->index();
            $table->unsignedBigInteger('initiated_by')->nullable();
            $table->string('mode');
            $table->json('configuration');
            $table->json('mapping');
            $table->unsignedInteger('changed_count');
            $table->timestamp('restored_at')->nullable();
            $table->unsignedBigInteger('restored_by')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('student_id_regeneration_batches');
    }
};
