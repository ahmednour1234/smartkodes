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
        Schema::create('records', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('work_order_id')->constrained('work_orders')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('form_version_id')->constrained('form_versions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->tinyInteger('status')->default(0)->check('status IN (0,1,2)');
            $table->timestampTz('submitted_at')->nullable();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUlid('updated_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->index(['tenant_id', 'work_order_id', 'status']);
            $table->index(['tenant_id', 'form_version_id']);
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};
