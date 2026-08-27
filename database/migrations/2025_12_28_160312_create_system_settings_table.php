<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DEFAULT_WEIGHTS = [
        [
            'key'         => 'default_assignment_weight',
            'value'       => '20',
            'type'        => 'integer',
            'description' => 'Default weight percentage for assignments in assessment scoring',
        ],
        [
            'key'         => 'default_mid_semester_weight',
            'value'       => '20',
            'type'        => 'integer',
            'description' => 'Default weight percentage for mid-semester exam in assessment scoring',
        ],
        [
            'key'         => 'default_end_semester_weight',
            'value'       => '60',
            'type'        => 'integer',
            'description' => 'Default weight percentage for end-semester exam in assessment scoring',
        ],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique()->index(); // index + unique = faster lookups
                $table->mediumText('value')->nullable(); // mediumText is usually enough & saves space vs text
                $table->string('type', 20)->default('string');
                $table->text('description')->nullable();
                $table->timestamps();

                // Optional but very useful for settings table
                $table->boolean('is_active')->default(true);
                $table->string('group')->nullable()->index(); // e.g. 'assessment', 'system', 'email', etc.
            });
        }

        $this->ensureBrandingColumnsExist();

        // Better way: use upsert + prepared data
        $now = now();

        $data = collect(self::DEFAULT_WEIGHTS)
            ->map(fn($item) => array_merge($item, [
                'created_at' => $now,
                'updated_at' => $now,
                'is_active'  => true,
                'group'      => 'assessment_weights',
            ]))
            ->all();

        DB::table('system_settings')->upsert(
            $data,
            ['key'], // unique by
            ['value', 'type', 'description', 'updated_at', 'is_active', 'group'] // fields to update if exists
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }

    /**
     * Some older production installations created this table before this
     * migration completed.  Make the migration resumable so that a later
     * deployment can finish it instead of failing because the table exists.
     */
    private function ensureBrandingColumnsExist(): void
    {
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
};
