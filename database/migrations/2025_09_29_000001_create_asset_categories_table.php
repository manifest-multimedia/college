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
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->unique();
            $table->text('description')->nullable();
            $table->bigInteger('parent_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('asset_categories')->onDelete('set null');
            $table->index(['parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_categories');
    }
};