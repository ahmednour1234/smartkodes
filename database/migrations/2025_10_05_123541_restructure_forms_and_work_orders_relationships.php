<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration restructures the relationships:
     * 1. Removes project_id from forms table (forms become standalone templates)
     * 2. Removes form_id from work_orders table (single form relationship)
     * 3. Creates form_work_order pivot table (many-to-many relationship)
     */
    public function up(): void
    {
        // Step 1: Create the pivot table for many-to-many relationship
        if (!Schema::hasTable('form_work_order')) {
            Schema::create('form_work_order', function (Blueprint $table) {
                $table->ulid('id')->primary();
                $table->foreignUlid('work_order_id')->constrained('work_orders')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignUlid('form_id')->constrained('forms')->cascadeOnUpdate()->cascadeOnDelete();
                $table->integer('order')->default(0); // Order of forms in the work order
                $table->timestampsTz();

                // Prevent duplicate form assignments to same work order
                $table->unique(['work_order_id', 'form_id']);
                $table->index(['work_order_id', 'order']);
            });
        }

        // Step 2: Remove project_id from forms table (make forms standalone)
        if (Schema::hasColumn('forms', 'project_id')) {
            Schema::table('forms', function (Blueprint $table) {
                // Drop the unique index first (not dependent on foreign key)
                $table->dropUnique(['tenant_id', 'project_id', 'name', 'deleted_at']);
            });
            
            Schema::table('forms', function (Blueprint $table) {
                // Drop foreign key and column together (will auto-drop related indexes)
                $table->dropForeign(['project_id']);
                $table->dropColumn('project_id');
            });
            
            Schema::table('forms', function (Blueprint $table) {
                // Add new indexes without project_id
                $table->unique(['tenant_id', 'name', 'deleted_at']);
                $table->index(['tenant_id', 'status']);
            });
        }

        // Step 3: Remove form_id from work_orders table (migrate to many-to-many)
        if (Schema::hasColumn('work_orders', 'form_id')) {
            // Migrate existing data to pivot table first
            DB::statement("
                INSERT INTO form_work_order (id, work_order_id, form_id, `order`, created_at, updated_at)
                SELECT 
                    UNHEX(REPLACE(UUID(), '-', '')),
                    id,
                    form_id,
                    0,
                    NOW(),
                    NOW()
                FROM work_orders
                WHERE form_id IS NOT NULL
            ");

            Schema::table('work_orders', function (Blueprint $table) {
                // Drop foreign key and column
                $table->dropForeign(['form_id']);
                $table->dropColumn('form_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Re-add project_id to forms table
        Schema::table('forms', function (Blueprint $table) {
            // Drop new indexes
            $table->dropUnique(['tenant_id', 'name', 'deleted_at']);
            $table->dropIndex(['tenant_id', 'status']);

            // Add back project_id
            $table->foreignUlid('project_id')->nullable()->after('tenant_id')->constrained('projects')->cascadeOnUpdate()->cascadeOnDelete();

            // Restore old indexes
            $table->unique(['tenant_id', 'project_id', 'name', 'deleted_at']);
            $table->index(['tenant_id', 'project_id', 'status']);
        });

        // Step 2: Re-add form_id to work_orders table
        Schema::table('work_orders', function (Blueprint $table) {
            $table->foreignUlid('form_id')->nullable()->after('project_id')->constrained('forms')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // Step 3: Migrate data back from pivot table to work_orders.form_id (take first form only)
        DB::statement("
            UPDATE work_orders
            SET form_id = (
                SELECT form_id
                FROM form_work_order
                WHERE form_work_order.work_order_id = work_orders.id
                ORDER BY `order` ASC
                LIMIT 1
            )
        ");

        // Step 4: Drop the pivot table
        Schema::dropIfExists('form_work_order');
    }
};
