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
        Schema::table('records', function (Blueprint $table) {
            // Add project_id and form_id if they don't exist
            if (!Schema::hasColumn('records', 'project_id')) {
                $table->foreignUlid('project_id')->nullable()->after('tenant_id')->constrained('projects')->cascadeOnUpdate()->nullOnDelete();
            }
            if (!Schema::hasColumn('records', 'form_id')) {
                $table->foreignUlid('form_id')->nullable()->after('project_id')->constrained('forms')->cascadeOnUpdate()->nullOnDelete();
            }
            if (!Schema::hasColumn('records', 'submitted_by')) {
                $table->foreignUlid('submitted_by')->nullable()->after('form_version_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }
            if (!Schema::hasColumn('records', 'location')) {
                $table->json('location')->nullable()->after('submitted_at');
            }
            if (!Schema::hasColumn('records', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('location');
            }
            if (!Schema::hasColumn('records', 'form_version')) {
                $table->integer('form_version')->default(1)->after('form_id');
            }

            // Add indexes for better query performance
            if (!Schema::hasIndex('records', ['tenant_id', 'project_id'])) {
                $table->index(['tenant_id', 'project_id']);
            }
            if (!Schema::hasIndex('records', ['tenant_id', 'form_id'])) {
                $table->index(['tenant_id', 'form_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('records', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['form_id']);
            $table->dropForeign(['submitted_by']);
            $table->dropColumn(['project_id', 'form_id', 'submitted_by', 'location', 'ip_address', 'form_version']);
        });
    }
};
