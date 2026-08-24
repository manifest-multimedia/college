<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->ulid('public_id')->nullable()->after('id');
            $table->unique('public_id');
        });

        DB::table('elections')
            ->whereNull('public_id')
            ->orderBy('id')
            ->chunkById(200, function ($elections): void {
                foreach ($elections as $election) {
                    DB::table('elections')
                        ->where('id', $election->id)
                        ->update(['public_id' => (string) Str::ulid()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->dropUnique(['public_id']);
            $table->dropColumn('public_id');
        });
    }
};
