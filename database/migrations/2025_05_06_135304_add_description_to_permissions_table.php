<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::table('permissions', function (Blueprint $table) {
                // Check if the column doesn't already exist
                if (!Schema::hasColumn('permissions', 'description')) {
                    $table->string('description')->nullable()->after('name');
                    Log::info('Added description column to permissions table');
                } else {
                    Log::info('Description column already exists in permissions table');
                }
            });
        } catch (\Exception $e) {
            Log::error('Migration error when adding description to permissions table: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
