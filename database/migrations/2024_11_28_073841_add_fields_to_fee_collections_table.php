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
        Schema::table('fee_collections', function (Blueprint $table) {
            // change id to increments id
            $table->increments('id')->change();
            $table->string('student_id')->nullable();
            $table->string('student_name')->nullable();
            $table->boolean('is_eligble')->nullable();
            $table->string('amount')->nullable();
            $table->string('status')->nullable();
            $table->string('remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_collections', function (Blueprint $table) {
            $table->dropColumn(['student_id', 'student_name', 'is_eligible', 'amount', 'status', 'remarks']);
        });
    }
};
