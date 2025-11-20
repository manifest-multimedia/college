<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add the 'Auditor' role
        Role::firstOrCreate(['name' => 'Auditor']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the 'Auditor' role
        Role::where('name', 'Auditor')->delete();
    }
};
