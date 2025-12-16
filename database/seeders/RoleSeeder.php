<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantId = \Illuminate\Support\Facades\DB::table('tenants')->where('slug', 'default')->value('id');

        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super_admin', 'description' => 'Platform owner with full access across all tenants.'],
            ['name' => 'Tenant Admin', 'slug' => 'tenant_admin', 'description' => 'Full access within their tenant organization.'],
            ['name' => 'Manager', 'slug' => 'manager', 'description' => 'Can manage assigned projects and work orders.'],
            ['name' => 'Auditor', 'slug' => 'auditor', 'description' => 'Read-only access for auditing and reporting.'],
            ['name' => 'Field Worker', 'slug' => 'field_worker', 'description' => 'Can submit records and access assigned work.'],
        ];

        foreach ($roles as $role) {
            \Illuminate\Support\Facades\DB::table('roles')->insert([
                'id' => (string) \Illuminate\Support\Str::ulid(),
                'tenant_id' => $tenantId,
                'name' => $role['name'],
                'slug' => $role['slug'],
                'description' => $role['description'],
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
