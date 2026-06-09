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
        Schema::table('student_fee_bill_items', function (Blueprint $table) {
            $table->decimal('amount_paid', 10, 2)->default(0.00)->after('amount');
            $table->decimal('balance', 10, 2)->default(0.00)->after('amount_paid');
            $table->string('status')->default('pending')->after('balance');
        });

        Schema::table('fee_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('student_fee_bill_item_id')->nullable()->after('student_fee_bill_id');
            $table->foreign('student_fee_bill_item_id')->references('id')->on('student_fee_bill_items')->onDelete('set null');
            $table->string('external_receipt')->nullable()->after('receipt_number');
        });

        // Initialize balance to amount for all existing bill items
        \DB::table('student_fee_bill_items')->update([
            'balance' => \DB::raw('amount'),
            'amount_paid' => 0.00,
            'status' => 'pending',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropForeign(['student_fee_bill_item_id']);
            $table->dropColumn(['student_fee_bill_item_id', 'external_receipt']);
        });

        Schema::table('student_fee_bill_items', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'balance', 'status']);
        });
    }
};
