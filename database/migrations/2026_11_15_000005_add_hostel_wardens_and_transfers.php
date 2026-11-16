<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('hostel_wardens', function(Blueprint $t) { $t->id(); $t->foreignId('hostel_id')->constrained('hostels')->restrictOnDelete(); $t->foreignId('user_id')->constrained('users')->restrictOnDelete(); $t->boolean('active')->default(true); $t->timestamps(); $t->unique(['hostel_id','user_id']); });
  Schema::table('hostel_allocations', function(Blueprint $t) { $t->foreignId('transferred_from_id')->nullable()->constrained('hostel_allocations')->nullOnDelete(); });
 }
 public function down(): void { Schema::table('hostel_allocations',fn(Blueprint $t)=>$t->dropConstrainedForeignId('transferred_from_id')); Schema::dropIfExists('hostel_wardens'); }
};
