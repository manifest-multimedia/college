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
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_fee_bill_id')->constrained();
            $table->unsignedInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method'); // cash, mobile_money, bank_transfer, check, etc.
            $table->string('reference_number')->unique();
            $table->string('receipt_number')->nullable()->unique();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('recorded_by'); // User ID who recorded the payment
            $table->foreign('recorded_by')->references('id')->on('users');
            $table->timestamp('payment_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};