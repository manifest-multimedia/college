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
        Schema::create('addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('country_of_citizenship')->nullable();
            $table->string('country_of_residence')->nullable();
            $table->string('home_region')->nullable();
            $table->string('home_town')->nullable();
            $table->string('gps_address')->nullable();
            $table->string('postal_address')->nullable();
            $table->string('residential_address')->nullable();
            $table->morphs('addressable');
            $table->string('slug')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
