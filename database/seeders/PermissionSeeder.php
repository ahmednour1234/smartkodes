<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantId = \Illuminate\Support\Facades\DB::table('tenants')->where('slug', 'default')->value('id');

        $permissions = [
            // Super Admin permissions
            ['name' => 'Manage Tenants', 'slug' => 'manage_tenants', 'description' => 'Create, suspend, reactivate tenants'],
            ['name' => 'Manage Plans', 'slug' => 'manage_plans', 'description' => 'Create and modify subscription plans'],
            ['name' => 'View Global Reports', 'slug' => 'view_global_reports', 'description' => 'Access platform-wide analytics'],
            ['name' => 'Manage Feature Flags', 'slug' => 'manage_feature_flags', 'description' => 'Control feature availability'],
            ['name' => 'Impersonate Users', 'slug' => 'impersonate_users', 'description' => 'Access user accounts for support'],

            // Tenant Admin permissions
            ['name' => 'Manage Organization', 'slug' => 'manage_organization', 'description' => 'Update tenant profile and settings'],
            ['name' => 'Manage Users', 'slug' => 'manage_users', 'description' => 'Create, edit, delete users'],
            ['name' => 'Manage Roles', 'slug' => 'manage_roles', 'description' => 'Create and assign roles'],
            ['name' => 'View Billing', 'slug' => 'view_billing', 'description' => 'Access subscription and billing info'],

            // Projects
            ['name' => 'Create Projects', 'slug' => 'create_projects'],
            ['name' => 'Edit Projects', 'slug' => 'edit_projects'],
            ['name' => 'Delete Projects', 'slug' => 'delete_projects'],
            ['name' => 'View Projects', 'slug' => 'view_projects'],

            // Forms
            ['name' => 'Create Forms', 'slug' => 'create_forms'],
            ['name' => 'Edit Forms', 'slug' => 'edit_forms'],
            ['name' => 'Delete Forms', 'slug' => 'delete_forms'],
            ['name' => 'View Forms', 'slug' => 'view_forms'],
            ['name' => 'Publish Forms', 'slug' => 'publish_forms'],

            // Work Orders
            ['name' => 'Create Work Orders', 'slug' => 'create_work_orders'],
            ['name' => 'Edit Work Orders', 'slug' => 'edit_work_orders'],
            ['name' => 'Delete Work Orders', 'slug' => 'delete_work_orders'],
            ['name' => 'View Work Orders', 'slug' => 'view_work_orders'],
            ['name' => 'Assign Work Orders', 'slug' => 'assign_work_orders'],

            // Records
            ['name' => 'Submit Records', 'slug' => 'submit_records'],
            ['name' => 'Edit Records', 'slug' => 'edit_records'],
            ['name' => 'Delete Records', 'slug' => 'delete_records'],
            ['name' => 'View Records', 'slug' => 'view_records'],
            ['name' => 'Export Records', 'slug' => 'export_records'],

            // Files
            ['name' => 'Upload Files', 'slug' => 'upload_files'],
            ['name' => 'Delete Files', 'slug' => 'delete_files'],
            ['name' => 'View Files', 'slug' => 'view_files'],

            // Reports
            ['name' => 'View Reports', 'slug' => 'view_reports'],
            ['name' => 'Export Reports', 'slug' => 'export_reports'],

            // Audit
            ['name' => 'View Audit Logs', 'slug' => 'view_audit_logs'],
        ];

        foreach ($permissions as $permission) {
            \Illuminate\Support\Facades\DB::table('permissions')->insert([
                'id' => (string) \Illuminate\Support\Str::ulid(),
                'tenant_id' => $tenantId,
                'name' => $permission['name'],
                'slug' => $permission['slug'],
                'description' => null,
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
