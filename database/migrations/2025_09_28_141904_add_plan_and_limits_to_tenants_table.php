<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->ulid('plan_id')->nullable()->after('domain');
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
            $table->unsignedInteger('storage_quota')->default(1000)->after('plan_id'); // MB
            $table->unsignedInteger('api_rate_limit')->default(1000)->after('storage_quota'); // requests per hour
            $table->ulid('created_by')->nullable()->after('api_rate_limit');
            $table->ulid('updated_by')->nullable()->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn(['plan_id', 'storage_quota', 'api_rate_limit', 'created_by', 'updated_by']);
        });
    }
};
