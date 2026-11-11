<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_attachments', function (Blueprint $table) {
            $table->enum('attachment_type', ['image', 'table', 'video', 'audio'])->default('image')->change();
            $table->string('disk')->default('exams');
        });
    }

    public function down(): void
    {
        if (DB::table('question_attachments')->whereIn('attachment_type', ['audio', 'video'])->orWhere('disk', 'question_media')->exists()) {
            throw new RuntimeException('Cannot roll back while private media or audio/video attachments exist.');
        }
        Schema::table('question_attachments', function (Blueprint $table) {
            $table->dropColumn('disk');
            $table->enum('attachment_type', ['image', 'table'])->default('image')->change();
        });
    }
};
