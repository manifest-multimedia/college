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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default assessment weight settings
        DB::table('system_settings')->insert([
            [
                'key' => 'default_assignment_weight',
                'value' => '20',
                'type' => 'integer',
                'description' => 'Default weight percentage for assignments in assessment scoring',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'default_mid_semester_weight',
                'value' => '20',
                'type' => 'integer',
                'description' => 'Default weight percentage for mid-semester exam in assessment scoring',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'default_end_semester_weight',
                'value' => '60',
                'type' => 'integer',
                'description' => 'Default weight percentage for end-semester exam in assessment scoring',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
