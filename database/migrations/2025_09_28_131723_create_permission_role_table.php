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
        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('permission_id')->constrained('permissions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('role_id')->constrained('roles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->primary(['tenant_id', 'permission_id', 'role_id']);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_role');
    }
};
