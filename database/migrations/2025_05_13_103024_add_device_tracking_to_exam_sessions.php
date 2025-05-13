<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            // Add session token to identify client device sessions
            $table->string('session_token', 100)->nullable()->after('auto_submitted');
            
            // Store device information (browser, OS, IP) as JSON
            $table->text('device_info')->nullable()->after('session_token');
            
            // Track when the session was last active
            $table->timestamp('last_activity')->nullable()->after('device_info');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->dropColumn(['session_token', 'device_info', 'last_activity']);
        });
    }
};
