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
        Schema::create('record_fields', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('record_id')->constrained('records')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('form_field_id')->constrained('form_fields')->cascadeOnUpdate()->cascadeOnDelete();
            $table->json('value_json');
            $table->index(['tenant_id', 'record_id']);
            $table->index(['tenant_id', 'form_field_id']);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_fields');
    }
};
