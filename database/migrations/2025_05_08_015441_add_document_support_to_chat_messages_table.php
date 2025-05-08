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
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('message');
            $table->boolean('is_document')->default(false)->after('type');
            $table->string('file_name')->nullable()->after('file_path');
            $table->string('mime_type')->nullable()->after('file_name');
            $table->integer('file_size')->nullable()->after('mime_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn('file_path');
            $table->dropColumn('is_document');
            $table->dropColumn('file_name');
            $table->dropColumn('mime_type');
            $table->dropColumn('file_size');
        });
    }
};
