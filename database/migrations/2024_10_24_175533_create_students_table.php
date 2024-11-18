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
        Schema::create('students', function (Blueprint $table) {
            $table->increments('id');
            $table->string('student_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('other_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('country_of_residence')->nullable();
            $table->string('home_region')->nullable();
            $table->string('home_town')->nullable();
            $table->string('religion')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('email')->nullable();
            $table->string('gps_address')->nullable();
            $table->string('postal_address')->nullable();
            $table->string('residential_address')->nullable();
            $table->string('marital_status')->nullable();
            $table->unsignedInteger('college_class_id')->nullable();
            $table->foreign('college_class_id')->references('id')->on('college_classes')->onDelete('set null');
            $table->unsignedInteger('cohort_id')->nullable();
            $table->foreign('cohort_id')->references('id')->on('cohorts')->onDelete('set null');
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
