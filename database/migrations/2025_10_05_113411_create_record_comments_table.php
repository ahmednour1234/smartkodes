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
        Schema::create('record_comments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('tenant_id');
            $table->ulid('record_id');
            $table->ulid('user_id');
            $table->ulid('parent_id')->nullable(); // For threaded comments
            $table->text('comment');
            $table->json('mentions')->nullable(); // Array of mentioned user IDs
            $table->json('attachments')->nullable(); // Array of file references
            $table->boolean('is_internal')->default(false); // Internal notes vs public comments
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['tenant_id', 'record_id']);
            $table->index(['tenant_id', 'user_id']);
            $table->index('parent_id');

            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('record_comments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_comments');
    }
};
