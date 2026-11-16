<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('hostel_settings',function(Blueprint $t){$t->foreignId('fee_type_id')->nullable()->constrained('fee_types')->nullOnDelete();$t->unsignedTinyInteger('minimum_payment_percent')->default(100);$t->boolean('auto_waitlist')->default(true);});
  Schema::table('hostel_applications',function(Blueprint $t){$t->timestamp('waitlisted_at')->nullable();});
 }
 public function down(): void {Schema::table('hostel_applications',fn(Blueprint $t)=>$t->dropColumn('waitlisted_at'));Schema::table('hostel_settings',function(Blueprint $t){$t->dropConstrainedForeignId('fee_type_id');$t->dropColumn(['minimum_payment_percent','auto_waitlist']);});}
};
