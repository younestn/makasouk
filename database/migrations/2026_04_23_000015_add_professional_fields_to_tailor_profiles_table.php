<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tailor_profiles', function (Blueprint $table): void {
            if (! Schema::hasColumn('tailor_profiles', 'specialization')) {
                $table->string('specialization')->nullable()->after('category_id')->index();
            }

            if (! Schema::hasColumn('tailor_profiles', 'work_wilaya')) {
                $table->string('work_wilaya')->nullable()->after('specialization')->index();
            }

            if (! Schema::hasColumn('tailor_profiles', 'years_of_experience')) {
                $table->unsignedSmallInteger('years_of_experience')->nullable()->after('work_wilaya');
            }

            if (! Schema::hasColumn('tailor_profiles', 'gender')) {
                $table->string('gender', 16)->nullable()->after('years_of_experience');
            }

            if (! Schema::hasColumn('tailor_profiles', 'workers_count')) {
                $table->unsignedInteger('workers_count')->nullable()->after('gender');
            }

            if (! Schema::hasColumn('tailor_profiles', 'commercial_register_path')) {
                $table->string('commercial_register_path')->nullable()->after('workers_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tailor_profiles', function (Blueprint $table): void {
            if (Schema::hasColumn('tailor_profiles', 'commercial_register_path')) {
                $table->dropColumn('commercial_register_path');
            }

            if (Schema::hasColumn('tailor_profiles', 'workers_count')) {
                $table->dropColumn('workers_count');
            }

            if (Schema::hasColumn('tailor_profiles', 'gender')) {
                $table->dropColumn('gender');
            }

            if (Schema::hasColumn('tailor_profiles', 'years_of_experience')) {
                $table->dropColumn('years_of_experience');
            }

            if (Schema::hasColumn('tailor_profiles', 'work_wilaya')) {
                $table->dropIndex('tailor_profiles_work_wilaya_index');
                $table->dropColumn('work_wilaya');
            }

            if (Schema::hasColumn('tailor_profiles', 'specialization')) {
                $table->dropIndex('tailor_profiles_specialization_index');
                $table->dropColumn('specialization');
            }
        });
    }
};

