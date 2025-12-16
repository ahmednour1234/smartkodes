<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove auto_increment from id before dropping primary key
        DB::statement('ALTER TABLE users MODIFY id BIGINT UNSIGNED NOT NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->dropPrimary();
            $table->dropColumn('id');
            $table->ulid('id');
            $table->primary('id');
            $table->dropUnique(['email']);
            $table->ulid('tenant_id')->nullable();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnUpdate()->nullOnDelete();
            $table->ulid('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnUpdate()->nullOnDelete();
            $table->ulid('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->cascadeOnUpdate()->nullOnDelete();
            $table->dropTimestamps();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unique(['email', 'deleted_at']); // Super admins have null tenant_id
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email', 'deleted_at']);
            $table->dropForeign(['tenant_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropSoftDeletesTz();
            $table->dropTimestampsTz();
            $table->timestamps();
            $table->dropPrimary();
            $table->dropColumn('id');
            $table->id();
            $table->string('email')->unique()->change();
        });

        DB::statement('ALTER TABLE users MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
    }
};
