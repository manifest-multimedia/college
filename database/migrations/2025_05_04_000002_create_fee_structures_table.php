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
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_type_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('college_class_id');
            $table->foreign('college_class_id')->references('id')->on('college_classes');
            $table->unsignedInteger('academic_year_id')->nullable();
            $table->foreign('academic_year_id')->references('id')->on('academic_years');
            $table->unsignedInteger('semester_id')->nullable();
            $table->foreign('semester_id')->references('id')->on('semesters');
            $table->decimal('amount', 10, 2);
            $table->boolean('is_mandatory')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Unique constraint for the combination
            $table->unique(['fee_type_id', 'college_class_id', 'academic_year_id', 'semester_id'], 'unique_fee_structure');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};