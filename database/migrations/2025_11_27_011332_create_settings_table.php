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
        // Create settings table if it doesn't exist
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        } else {
            // If table exists, add missing columns
            Schema::table('settings', function (Blueprint $table) {
                // Add 'key' column if it doesn't exist
                if (!Schema::hasColumn('settings', 'key')) {
                    $table->string('key')->unique()->after('id');
                }

                // Add 'value' column if it doesn't exist
                if (!Schema::hasColumn('settings', 'value')) {
                    $table->text('value')->nullable()->after('key');
                }

                // Add 'description' column if it doesn't exist
                if (!Schema::hasColumn('settings', 'description')) {
                    $table->text('description')->nullable()->after('value');
                }

                // Add timestamps if they don't exist
                if (!Schema::hasColumn('settings', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
