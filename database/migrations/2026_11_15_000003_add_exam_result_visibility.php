<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('online_exam_result_policies', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->string('release_mode')->default('immediate');
            $table->string('detail_level')->default('score');
            $table->timestamps();
        });
        Schema::table('exams', function (Blueprint $table) {
            $table->string('result_release_override')->nullable();
            $table->string('result_detail_override')->nullable();
            $table->timestamp('results_published_at')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['result_release_override', 'result_detail_override', 'results_published_at']);
        });
        Schema::dropIfExists('online_exam_result_policies');
    }
};
