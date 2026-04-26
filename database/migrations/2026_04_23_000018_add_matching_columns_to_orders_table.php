<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'delivery_work_wilaya')) {
                $table->string('delivery_work_wilaya')->nullable()->after('delivery_longitude')->index();
            }

            if (! Schema::hasColumn('orders', 'delivery_location_label')) {
                $table->string('delivery_location_label')->nullable()->after('delivery_work_wilaya');
            }

            if (! Schema::hasColumn('orders', 'matched_specialization')) {
                $table->string('matched_specialization', 64)->nullable()->after('status')->index();
            }

            if (! Schema::hasColumn('orders', 'matching_snapshot')) {
                $table->json('matching_snapshot')->nullable()->after('matched_specialization');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'matching_snapshot')) {
                $table->dropColumn('matching_snapshot');
            }

            if (Schema::hasColumn('orders', 'matched_specialization')) {
                $table->dropIndex('orders_matched_specialization_index');
                $table->dropColumn('matched_specialization');
            }

            if (Schema::hasColumn('orders', 'delivery_location_label')) {
                $table->dropColumn('delivery_location_label');
            }

            if (Schema::hasColumn('orders', 'delivery_work_wilaya')) {
                $table->dropIndex('orders_delivery_work_wilaya_index');
                $table->dropColumn('delivery_work_wilaya');
            }
        });
    }
};
