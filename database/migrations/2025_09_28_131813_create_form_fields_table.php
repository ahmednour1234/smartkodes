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
        Schema::create('form_fields', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('form_id')->constrained('forms')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name');
            $table->string('type');
            $table->json('config_json')->nullable();
            $table->integer('order')->default(0);
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unique(['tenant_id', 'form_id', 'name', 'deleted_at']);
            $table->index(['tenant_id', 'form_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
