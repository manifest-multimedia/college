<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Keep wrongly generated bills as an auditable record while allowing a
     * corrected bill to be generated for the same student, year and semester.
     */
    public function up(): void
    {
        Schema::table('student_fee_bills', function (Blueprint $table) {
            $table->timestamp('reversed_at')->nullable()->after('billing_date');
            $table->foreignId('reversed_by')->nullable()->after('reversed_at')->constrained('users')->nullOnDelete();
            $table->text('reversal_reason')->nullable()->after('reversed_by');
            $table->index('reversed_at');
        });
    }

    public function down(): void
    {
        Schema::table('student_fee_bills', function (Blueprint $table) {
            $table->dropForeign(['reversed_by']);
            $table->dropIndex(['reversed_at']);
            $table->dropColumn(['reversed_at', 'reversed_by', 'reversal_reason']);
        });
    }
};
