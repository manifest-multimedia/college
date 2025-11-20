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
        Schema::create('student_fee_bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_fee_bill_id')->constrained()->onDelete('cascade');
            $table->foreignId('fee_type_id')->constrained();
            $table->foreignId('fee_structure_id')->nullable()->constrained();
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_fee_bill_items');
    }
};
