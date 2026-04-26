<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            if (! Schema::hasColumn('categories', 'tailor_specialization')) {
                $table->string('tailor_specialization', 64)->nullable()->after('slug');
                $table->index('tailor_specialization');
            }
        });

        if (Schema::hasColumn('categories', 'tailor_specialization')) {
            DB::statement(<<<'SQL'
                WITH ranked_specializations AS (
                    SELECT
                        tp.category_id,
                        tp.specialization,
                        COUNT(*) AS specialization_count,
                        ROW_NUMBER() OVER (
                            PARTITION BY tp.category_id
                            ORDER BY COUNT(*) DESC, tp.specialization ASC
                        ) AS row_num
                    FROM tailor_profiles tp
                    WHERE tp.category_id IS NOT NULL
                        AND tp.specialization IS NOT NULL
                    GROUP BY tp.category_id, tp.specialization
                )
                UPDATE categories c
                SET tailor_specialization = rs.specialization
                FROM ranked_specializations rs
                WHERE c.id = rs.category_id
                    AND rs.row_num = 1
                    AND c.tailor_specialization IS NULL
            SQL);

            DB::table('categories')
                ->whereNull('tailor_specialization')
                ->update(['tailor_specialization' => 'Regular sewing']);

            DB::statement("ALTER TABLE categories ALTER COLUMN tailor_specialization SET DEFAULT 'Regular sewing'");
            DB::statement('ALTER TABLE categories ALTER COLUMN tailor_specialization SET NOT NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('categories', 'tailor_specialization')) {
            return;
        }

        DB::statement('ALTER TABLE categories ALTER COLUMN tailor_specialization DROP NOT NULL');
        DB::statement('ALTER TABLE categories ALTER COLUMN tailor_specialization DROP DEFAULT');

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropIndex('categories_tailor_specialization_index');
            $table->dropColumn('tailor_specialization');
        });
    }
};
