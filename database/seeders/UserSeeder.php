<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            // Get roles for this tenant
            $adminRole = Role::where('slug', 'tenant_admin')->first();
            $managerRole = Role::where('slug', 'manager')->first();
            $fieldWorkerRole = Role::where('slug', 'field_worker')->first();
            
            // Create Tenant Admin (already created in DatabaseSeeder for default tenant)
            if ($tenant->slug !== 'default') {
                $admin = User::create([
                    'tenant_id' => $tenant->id,
                    'name' => "{$tenant->name} Admin",
                    'email' => "admin@{$tenant->slug}.com",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'phone' => '+1-555-' . rand(1000, 9999),
                    'country' => 'US',
                ]);
                
                // Attach admin role
                $admin->roles()->attach($adminRole->id, [
                    'tenant_id' => $tenant->id,
                ]);
            } else {
                // Get the existing admin for default tenant
                $admin = User::where('email', 'admin@default.com')->first();
            }
            
            // Create 2-3 Project Managers per tenant
            $managerCount = rand(2, 3);
            for ($i = 1; $i <= $managerCount; $i++) {
                $manager = User::create([
                    'tenant_id' => $tenant->id,
                    'name' => "Manager {$i} - {$tenant->name}",
                    'email' => "manager{$i}@{$tenant->slug}.com",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'phone' => '+1-555-' . rand(1000, 9999),
                    'country' => 'US',
                ]);
                
                $manager->roles()->attach($managerRole->id, [
                    'tenant_id' => $tenant->id,
                ]);
            }
            
            // Create 5-8 Field Workers per tenant
            $workerCount = rand(5, 8);
            for ($i = 1; $i <= $workerCount; $i++) {
                $worker = User::create([
                    'tenant_id' => $tenant->id,
                    'name' => "Field Worker {$i} - {$tenant->name}",
                    'email' => "worker{$i}@{$tenant->slug}.com",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'phone' => '+1-555-' . rand(1000, 9999),
                    'country' => 'US',
                ]);
                
                $worker->roles()->attach($fieldWorkerRole->id, [
                    'tenant_id' => $tenant->id,
                ]);
            }
        }
    }
}
