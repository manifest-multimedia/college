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
        Schema::create('assets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('asset_tag', 50)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->bigInteger('category_id')->unsigned()->nullable();
            $table->string('location', 255)->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('current_value', 10, 2)->nullable();
            $table->enum('state', ['new', 'in_use', 'damaged', 'repaired', 'disposed', 'lost'])->default('new');
            $table->string('assigned_to_type', 255)->nullable();
            $table->bigInteger('assigned_to_id')->unsigned()->nullable();
            $table->text('notes')->nullable();
            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('category_id')->references('id')->on('asset_categories')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['asset_tag']);
            $table->index(['state']);
            $table->index(['category_id']);
            $table->index(['assigned_to_type', 'assigned_to_id']);
            $table->index(['created_by']);
            $table->index(['updated_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
