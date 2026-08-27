<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure all previously migrated installations can persist the branding
     * overrides used by the authentication and examination portal themes.
     */
    public function up(): void
    {
        if (! Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique()->index();
                $table->mediumText('value')->nullable();
                $table->string('type', 20)->default('string');
                $table->text('description')->nullable();
                $table->timestamps();
                $table->boolean('is_active')->default(true);
                $table->string('group')->nullable()->index();
            });

            return;
        }

        Schema::table('system_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('system_settings', 'type')) {
                $table->string('type', 20)->default('string');
            }

            if (! Schema::hasColumn('system_settings', 'description')) {
                $table->text('description')->nullable();
            }

            if (! Schema::hasColumn('system_settings', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('system_settings', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }

            if (! Schema::hasColumn('system_settings', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }

            if (! Schema::hasColumn('system_settings', 'group')) {
                $table->string('group')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        // This repair migration deliberately preserves institution settings.
    }
};
