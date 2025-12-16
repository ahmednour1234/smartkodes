<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::table('tenants')->insert([
            [
                'id' => (string) \Illuminate\Support\Str::ulid(),
                'name' => 'Default Tenant',
                'company_name' => null,
                'field_of_work' => null,
                'slug' => 'default',
                'domain' => 'default.smartkodes.com',
                'status' => 1, // Active
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) \Illuminate\Support\Str::ulid(),
                'name' => 'Acme Corporation',
                'company_name' => 'Acme Corp',
                'field_of_work' => 'Construction',
                'slug' => 'acme',
                'domain' => 'acme.smartkodes.com',
                'status' => 1, // Active
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) \Illuminate\Support\Str::ulid(),
                'name' => 'TechCorp Solutions',
                'company_name' => 'TechCorp Solutions Inc',
                'field_of_work' => 'Technology Services',
                'slug' => 'techcorp',
                'domain' => 'techcorp.smartkodes.com',
                'status' => 1, // Active
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
