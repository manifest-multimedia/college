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
        Schema::create('certificates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('year')->nullable();
            $table->string('issuer')->nullable();
            $table->string('number')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('description')->nullable();
            $table->string('slug')->nullable();
            $table->string('file_path')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
