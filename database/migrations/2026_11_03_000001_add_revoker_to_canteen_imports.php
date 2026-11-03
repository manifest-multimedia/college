<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canteen_qualification_imports', function (Blueprint $table) {
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('canteen_qualification_imports', fn (Blueprint $table) => $table->dropConstrainedForeignId('revoked_by'));
    }
};
