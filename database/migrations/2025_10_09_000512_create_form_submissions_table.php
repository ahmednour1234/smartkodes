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
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('form_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->json('data'); // Form field values
            $table->enum('status', ['draft', 'submitted', 'pending_review', 'approved', 'rejected', 'completed'])->default('submitted');
            $table->timestampTz('submitted_at')->nullable();
            $table->timestampTz('reviewed_at')->nullable();
            $table->foreignUlid('reviewed_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestampsTz();

            $table->index(['form_id', 'status']);
            $table->index(['tenant_id', 'submitted_at']);
            $table->index(['user_id', 'submitted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
