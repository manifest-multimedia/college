<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds gender targeting so fees (e.g. uniforms) can apply to All, Male only, or Female only.
     * Existing rows default to 'all' so current behaviour is unchanged.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('fee_structures', 'applicable_gender')) {
            Schema::table('fee_structures', function (Blueprint $table) {
                $table->string('applicable_gender', 10)->default('all')->after('is_active');
            });
        }

        // Add new unique constraint including applicable_gender first, then drop the old one (avoids FK constraint issues)
        $indexes = DB::select("SHOW INDEX FROM fee_structures WHERE Key_name = 'unique_fee_structure_with_gender'");
        if (empty($indexes)) {
            Schema::table('fee_structures', function (Blueprint $table) {
                $table->unique(
                    ['fee_type_id', 'college_class_id', 'academic_year_id', 'semester_id', 'applicable_gender'],
                    'unique_fee_structure_with_gender'
                );
            });
        }
        $oldIndexes = DB::select("SHOW INDEX FROM fee_structures WHERE Key_name = 'unique_fee_structure'");
        if (! empty($oldIndexes)) {
            Schema::table('fee_structures', function (Blueprint $table) {
                $table->dropUnique('unique_fee_structure');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->unique(['fee_type_id', 'college_class_id', 'academic_year_id', 'semester_id'], 'unique_fee_structure');
        });
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropUnique('unique_fee_structure_with_gender');
        });

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropColumn('applicable_gender');
        });
    }
};
