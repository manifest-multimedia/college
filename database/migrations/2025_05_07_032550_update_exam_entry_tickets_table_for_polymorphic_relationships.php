<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exam_entry_tickets', function (Blueprint $table) {
            // Add new polymorphic columns
            $table->string('ticketable_type')->nullable()->after('exam_type_id');
            $table->unsignedBigInteger('ticketable_id')->nullable()->after('ticketable_type');
            
            // Add index for polymorphic relationship
            $table->index(['ticketable_type', 'ticketable_id']);
        });
        
        // Add default values for existing records - assuming exam_type_id is related to exams
        DB::statement("UPDATE exam_entry_tickets SET ticketable_type = 'App\\\\Models\\\\Exam', ticketable_id = exam_type_id WHERE ticketable_type IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_entry_tickets', function (Blueprint $table) {
            // Remove the new polymorphic columns
            $table->dropIndex(['ticketable_type', 'ticketable_id']);
            $table->dropColumn(['ticketable_type', 'ticketable_id']);
        });
    }
};
