<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tailor_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('tailor_profiles', 'specialization')) {
                $table->dropColumn('specialization');
            }

            if (! Schema::hasColumn('tailor_profiles', 'category_id')) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('categories')
                    ->nullOnDelete();
            }

            $table->index(['category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('tailor_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('tailor_profiles', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }

            if (! Schema::hasColumn('tailor_profiles', 'specialization')) {
                $table->string('specialization')->nullable()->after('user_id');
                $table->index('specialization');
            }
        });
    }
};
