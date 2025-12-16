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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('project_id')->constrained('projects')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('form_id')->constrained('forms')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('assigned_to')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->tinyInteger('status')->default(0)->check('status IN (0,1,2,3)');
            $table->timestampTz('due_date')->nullable();
            $table->foreignUlid('created_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUlid('updated_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->index(['tenant_id', 'project_id', 'status', 'due_date']);
            $table->index(['tenant_id', 'assigned_to']);
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
