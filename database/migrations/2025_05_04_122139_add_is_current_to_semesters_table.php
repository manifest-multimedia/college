<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->boolean('is_current')->default(false)->after('description');
        });

        // Set the most recently created semester as current if any exist
        if (DB::table('semesters')->count() > 0) {
            DB::table('semesters')
                ->orderBy('created_at', 'desc')
                ->limit(1)
                ->update(['is_current' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropColumn('is_current');
        });
    }
};
