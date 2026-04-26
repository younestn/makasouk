<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'subtotal_amount')) {
                $table->decimal('subtotal_amount', 10, 2)->nullable()->after('matching_snapshot');
            }

            if (! Schema::hasColumn('orders', 'shipping_amount')) {
                $table->decimal('shipping_amount', 10, 2)->default(0)->after('subtotal_amount');
            }

            if (! Schema::hasColumn('orders', 'platform_commission_amount')) {
                $table->decimal('platform_commission_amount', 10, 2)->default(0)->after('shipping_amount');
            }

            if (! Schema::hasColumn('orders', 'tailor_net_amount')) {
                $table->decimal('tailor_net_amount', 10, 2)->default(0)->after('platform_commission_amount');
            }
        });

        Schema::table('tailor_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('tailor_profiles', 'score')) {
                $table->unsignedTinyInteger('score')->default(100)->after('total_reviews');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'pattern_file_path')) {
                $table->string('pattern_file_path')->nullable()->after('main_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'pattern_file_path')) {
                $table->dropColumn('pattern_file_path');
            }
        });

        Schema::table('tailor_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('tailor_profiles', 'score')) {
                $table->dropColumn('score');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            foreach (['tailor_net_amount', 'platform_commission_amount', 'shipping_amount', 'subtotal_amount'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
