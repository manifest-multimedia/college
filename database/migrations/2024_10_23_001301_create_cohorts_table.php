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
        Schema::create('cohorts', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('slug');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->string('created_by')->nullable();
            $table->string('academic_year')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cohorts');
    }
};
