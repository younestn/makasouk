<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'avatar_path')) {
                $table->string('avatar_path')->nullable()->after('phone');
            }
        });

        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'tracking_stage')) {
                $table->string('tracking_stage', 64)->nullable()->after('status')->index();
            }
        });

        Schema::create('tracking_events', function (Blueprint $table): void {
            $table->id();
            $table->morphs('trackable');
            $table->string('code', 64)->index();
            $table->string('responsible_role', 32)->nullable()->index();
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('occurred_at')->nullable()->index();
            $table->timestamps();

            $table->index(['trackable_type', 'trackable_id', 'occurred_at'], 'tracking_events_trackable_occurred_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_events');

        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'tracking_stage')) {
                $table->dropColumn('tracking_stage');
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'avatar_path')) {
                $table->dropColumn('avatar_path');
            }
        });
    }
};
