<?php
// database/migrations/2025_12_01_000000_add_location_and_priority_to_work_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('due_date');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');

            $table->unsignedInteger('priority_value')->nullable()->after('status');
            $table->enum('priority_unit', ['hour', 'day', 'week', 'month'])
                ->nullable()
                ->after('priority_value');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'priority_value', 'priority_unit']);
        });
    }
};
