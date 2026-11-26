<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_id_regeneration_operations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('cohort_id')->index();
            $table->unsignedBigInteger('active_cohort_id')->nullable()->unique();
            $table->unsignedBigInteger('initiated_by');
            $table->string('status');
            $table->unsignedInteger('total');
            $table->unsignedInteger('processed')->default(0);
            $table->uuid('batch_id')->nullable();
            $table->string('message')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('student_id_regeneration_operations');
    }
};
