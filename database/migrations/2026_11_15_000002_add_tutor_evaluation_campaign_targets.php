<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tutor_evaluation_campaigns', function (Blueprint $table) {
            $table->boolean('target_all')->default(true);
        });
        Schema::create('tutor_evaluation_campaign_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('tutor_evaluation_campaigns')->cascadeOnDelete();
            $table->unsignedInteger('subject_id');
            $table->unsignedBigInteger('lecturer_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->restrictOnDelete();
            $table->foreign('lecturer_id')->references('id')->on('users')->restrictOnDelete();
            $table->unique(['campaign_id', 'subject_id', 'lecturer_id'], 'tutor_eval_campaign_target_unique');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('tutor_evaluation_campaign_targets');
        Schema::table('tutor_evaluation_campaigns', fn (Blueprint $table) => $table->dropColumn('target_all'));
    }
};
