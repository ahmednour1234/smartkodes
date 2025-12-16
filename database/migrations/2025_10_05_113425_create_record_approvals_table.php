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
        Schema::create('record_approvals', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('tenant_id');
            $table->ulid('record_id');
            $table->ulid('approver_id');
            $table->ulid('requested_by')->nullable();
            $table->integer('sequence')->default(1); // Order in approval chain
            $table->string('status'); // pending, approved, rejected, delegated
            $table->text('comments')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->ulid('delegated_to')->nullable(); // If approval delegated to another user
            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'record_id']);
            $table->index(['tenant_id', 'approver_id']);
            $table->index('status');
            $table->index('sequence');

            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('delegated_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_approvals');
    }
};
