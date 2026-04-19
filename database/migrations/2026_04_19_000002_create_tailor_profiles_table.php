<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tailor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('specialization')->index();
            $table->enum('status', ['online', 'offline'])->default('offline')->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });

        DB::statement('ALTER TABLE tailor_profiles ADD COLUMN location geometry(Point, 4326)');
        DB::statement('CREATE INDEX tailor_profiles_location_gist ON tailor_profiles USING GIST (location)');
    }

    public function down(): void
    {
        Schema::dropIfExists('tailor_profiles');
    }
};
