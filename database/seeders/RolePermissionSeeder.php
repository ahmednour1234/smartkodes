<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Permission slugs assigned to each role slug, per the permissions matrix.
     */
    private array $matrix = [
        'tenant_admin' => [
            // Projects
            'create_projects', 'edit_projects', 'delete_projects', 'view_projects',
            // Forms
            'create_forms', 'edit_forms', 'delete_forms', 'view_forms', 'publish_forms',
            // Work Orders
            'create_work_orders', 'edit_work_orders', 'delete_work_orders', 'view_work_orders', 'assign_work_orders',
            // Records
            'submit_records', 'edit_records', 'delete_records', 'view_records', 'export_records',
            // Files
            'upload_files', 'delete_files', 'view_files',
            // Reports
            'view_reports', 'export_reports',
            // Users & Roles
            'manage_users', 'manage_roles',
            // Organization / Billing / Audit
            'manage_organization', 'view_billing', 'view_audit_logs',
        ],
        'manager' => [
            // Projects
            'create_projects', 'edit_projects', 'view_projects',
            // Forms (view + submit only)
            'view_forms',
            // Work Orders
            'create_work_orders', 'edit_work_orders', 'view_work_orders', 'assign_work_orders',
            // Records
            'submit_records', 'edit_records', 'view_records',
            // Files
            'upload_files', 'view_files',
            // Reports (read-only — no export)
            'view_reports',
        ],
        'field_worker' => [
            // Forms (view + submit only)
            'view_forms',
            // Work Orders (view assigned only)
            'view_work_orders',
            // Records
            'submit_records', 'view_records',
            // Files
            'upload_files', 'view_files',
        ],
    ];

    public function run(): void
    {
        $tenantIds = DB::table('tenants')->pluck('id');

        foreach ($tenantIds as $tenantId) {
            foreach ($this->matrix as $roleSlug => $permissionSlugs) {
                $role = DB::table('roles')
                    ->where('tenant_id', $tenantId)
                    ->where('slug', $roleSlug)
                    ->first();

                if (!$role) {
                    continue;
                }

                foreach ($permissionSlugs as $permSlug) {
                    $permission = DB::table('permissions')
                        ->where('tenant_id', $tenantId)
                        ->where('slug', $permSlug)
                        ->first();

                    if (!$permission) {
                        continue;
                    }

                    $exists = DB::table('permission_role')
                        ->where('tenant_id', $tenantId)
                        ->where('role_id', $role->id)
                        ->where('permission_id', $permission->id)
                        ->exists();

                    if (!$exists) {
                        DB::table('permission_role')->insert([
                            'tenant_id'     => $tenantId,
                            'role_id'       => $role->id,
                            'permission_id' => $permission->id,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }
                }
            }
        }
    }
}
