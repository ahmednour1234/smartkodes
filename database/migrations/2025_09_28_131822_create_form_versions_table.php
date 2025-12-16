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
        Schema::create('form_versions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('form_id')->constrained('forms')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('version');
            $table->json('schema_json');
            $table->foreignUlid('created_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->unique(['tenant_id', 'form_id', 'version']);
            $table->index(['tenant_id', 'form_id']);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_versions');
    }
};
