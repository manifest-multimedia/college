<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
};