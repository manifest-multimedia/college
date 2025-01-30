<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->integer('questions_per_session')
                ->nullable()
                ->after('duration')
                ->comment('Number of questions to be answered per session');
                
            $table->decimal('passing_percentage', 5, 2)
                ->nullable()
                ->after('questions_per_session')
                ->default(50.00)
                ->comment('Minimum percentage required to pass the exam');
        });
    }

    public function down()
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn([
                'questions_per_session',
                'passing_percentage'
            ]);
        });
    }
}; 