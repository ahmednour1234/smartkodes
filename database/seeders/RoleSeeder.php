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
        $defaultRoles = [
            ['name' => 'Admin',        'slug' => 'tenant_admin', 'description' => 'Full access within their tenant organization.'],
            ['name' => 'Manager',      'slug' => 'manager',      'description' => 'Can manage assigned projects and work orders.'],
            ['name' => 'Field Worker', 'slug' => 'field_worker', 'description' => 'Can submit records and access assigned work.'],
        ];

        $tenantIds = \Illuminate\Support\Facades\DB::table('tenants')->pluck('id');

        foreach ($tenantIds as $tenantId) {
            foreach ($defaultRoles as $role) {
                $exists = \Illuminate\Support\Facades\DB::table('roles')
                    ->where('tenant_id', $tenantId)
                    ->where('slug', $role['slug'])
                    ->exists();

                if (!$exists) {
                    \Illuminate\Support\Facades\DB::table('roles')->insert([
                        'id' => (string) \Illuminate\Support\Str::ulid(),
                        'tenant_id' => $tenantId,
                        'name' => $role['name'],
                        'slug' => $role['slug'],
                        'description' => $role['description'],
                        'status' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
