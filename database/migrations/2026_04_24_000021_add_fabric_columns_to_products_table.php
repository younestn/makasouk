<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('fabric_type')->nullable()->after('main_image');
            $table->string('fabric_country')->nullable()->after('fabric_type');
            $table->text('fabric_description')->nullable()->after('fabric_country');
            $table->string('fabric_image_path')->nullable()->after('fabric_description');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'fabric_type',
                'fabric_country',
                'fabric_description',
                'fabric_image_path',
            ]);
        });
    }
};

