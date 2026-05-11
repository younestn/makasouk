<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        // MySQL stores location data in decimal latitude/longitude columns.
        // This migration remains as a historical no-op for existing migration order.
    }

    public function down(): void
    {
        //
    }
};
