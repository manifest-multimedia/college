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
        Schema::table('election_votes', function (Blueprint $table) {
            $table->enum('vote_type', ['candidate', 'yes', 'no'])->default('candidate')->after('election_candidate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('election_votes', function (Blueprint $table) {
            $table->dropColumn('vote_type');
        });
    }
};
