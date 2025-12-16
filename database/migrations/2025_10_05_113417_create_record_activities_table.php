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
        Schema::create('record_activities', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('tenant_id');
            $table->ulid('record_id');
            $table->ulid('user_id')->nullable(); // Null for system actions
            $table->string('action'); // created, updated, status_changed, assigned, commented, approved, rejected
            $table->string('field_name')->nullable(); // For field-level changes
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('description')->nullable(); // Human-readable description
            $table->json('metadata')->nullable(); // Additional context
            $table->timestamp('created_at');

            // Indexes
            $table->index(['tenant_id', 'record_id']);
            $table->index(['tenant_id', 'user_id']);
            $table->index('action');
            $table->index('created_at');

            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_activities');
    }
};
