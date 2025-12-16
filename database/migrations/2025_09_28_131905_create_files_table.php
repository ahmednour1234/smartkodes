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
        Schema::create('files', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('record_id')->nullable()->constrained('records')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name');
            $table->string('path');
            $table->string('type');
            $table->bigInteger('size');
            $table->string('mime_type');
            $table->foreignUlid('created_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->index(['tenant_id', 'record_id']);
            $table->index(['tenant_id', 'created_by']);
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
