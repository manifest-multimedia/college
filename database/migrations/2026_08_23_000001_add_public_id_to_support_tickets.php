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
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->ulid('public_id')->nullable()->after('ticket_number');
            $table->unique('public_id');
        });

        DB::table('support_tickets')
            ->whereNull('public_id')
            ->orderBy('id')
            ->chunkById(200, function ($tickets): void {
                foreach ($tickets as $ticket) {
                    DB::table('support_tickets')
                        ->where('id', $ticket->id)
                        ->update(['public_id' => (string) Str::ulid()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropUnique(['public_id']);
            $table->dropColumn('public_id');
        });
    }
};
