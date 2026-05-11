<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'tailor_specialization')) {
                $table->string('tailor_specialization')->nullable()->after('description');
            }
        });

        $ranked = DB::table('tailor_profiles')
            ->select('category_id', 'specialization', DB::raw('COUNT(*) as total'))
            ->whereNotNull('category_id')
            ->whereNotNull('specialization')
            ->groupBy('category_id', 'specialization')
            ->orderBy('category_id')
            ->orderByDesc('total')
            ->orderBy('specialization')
            ->get()
            ->groupBy('category_id');

        foreach ($ranked as $categoryId => $rows) {
            $topSpecialization = $rows->first()?->specialization;

            if ($topSpecialization !== null) {
                DB::table('categories')
                    ->where('id', $categoryId)
                    ->whereNull('tailor_specialization')
                    ->update([
                        'tailor_specialization' => $topSpecialization,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'tailor_specialization')) {
                $table->dropColumn('tailor_specialization');
            }
        });
    }
};