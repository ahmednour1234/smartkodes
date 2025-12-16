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
        Schema::create('projects', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->tinyInteger('status')->default(0)->check('status IN (0,1,2)');
            $table->foreignUlid('created_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUlid('updated_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->unique(['tenant_id', 'name', 'deleted_at']);
            $table->index(['tenant_id', 'status', 'created_by']);
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
