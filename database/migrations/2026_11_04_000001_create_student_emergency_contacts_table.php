<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_emergency_contacts', function (Blueprint $table) {
            $table->id();
            // Match the unsigned integer key used by the established students table.
            $table->unsignedInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('relationship', 100);
            $table->string('mobile_number', 30);
            $table->string('alternate_phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('gps_address')->nullable();
            $table->text('residential_address')->nullable();
            // Stable import slots allow retries without replacing manually added contacts.
            $table->unsignedTinyInteger('import_slot')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'import_slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_emergency_contacts');
    }
};
