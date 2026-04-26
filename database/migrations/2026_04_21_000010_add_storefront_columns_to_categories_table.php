<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('description');
            $table->boolean('is_featured')->default(false)->index()->after('is_active');
            $table->unsignedInteger('sort_order')->default(0)->index()->after('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'is_featured', 'sort_order']);
        });
    }
};

