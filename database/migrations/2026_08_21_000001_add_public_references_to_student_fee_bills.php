<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds `public_reference` to student_fee_bills and student_fee_bill_items
     * then backfills existing records with unique ULID-based references.
     *
     * @return void
     */
    public function up()
    {
        // 1. Add nullable columns first
        Schema::table('student_fee_bills', function (Blueprint $table) {
            $table->string('public_reference', 64)->nullable()->after('bill_reference');
        });

        Schema::table('student_fee_bill_items', function (Blueprint $table) {
            $table->string('public_reference', 64)->nullable()->after('student_fee_bill_id');
        });

        // 2. Backfill existing student fee bills
        $bills = DB::table('student_fee_bills')->select('id', 'public_reference')->get();
        foreach ($bills as $bill) {
            if (empty($bill->public_reference)) {
                // generate unique reference
                do {
                    $ref = 'BILL-'.strtoupper(Str::ulid());
                    $exists = DB::table('student_fee_bills')->where('public_reference', $ref)->exists();
                } while ($exists);

                DB::table('student_fee_bills')->where('id', $bill->id)->update(['public_reference' => $ref]);
            }
        }

        // 3. Backfill existing student fee bill items
        $items = DB::table('student_fee_bill_items')->select('id', 'public_reference')->get();
        foreach ($items as $item) {
            if (empty($item->public_reference)) {
                do {
                    $ref = 'FEE-'.strtoupper(Str::ulid());
                    $exists = DB::table('student_fee_bill_items')->where('public_reference', $ref)->exists();
                } while ($exists);

                DB::table('student_fee_bill_items')->where('id', $item->id)->update(['public_reference' => $ref]);
            }
        }

        // 4. Make the columns non-nullable (safe after backfill)
        Schema::table('student_fee_bills', function (Blueprint $table) {
            $table->string('public_reference', 64)->unique()->nullable(false)->change();
        });

        Schema::table('student_fee_bill_items', function (Blueprint $table) {
            $table->string('public_reference', 64)->unique()->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_fee_bill_items', function (Blueprint $table) {
            if (Schema::hasColumn('student_fee_bill_items', 'public_reference')) {
                $table->dropUnique(['public_reference']);
                $table->dropColumn('public_reference');
            }
        });

        Schema::table('student_fee_bills', function (Blueprint $table) {
            if (Schema::hasColumn('student_fee_bills', 'public_reference')) {
                $table->dropUnique(['public_reference']);
                $table->dropColumn('public_reference');
            }
        });
    }
};
