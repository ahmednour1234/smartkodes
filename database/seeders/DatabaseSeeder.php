<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Phase 1: Foundation - Tenants, Plans, Roles, Permissions
        $this->command->info('🏗️  Phase 1: Setting up foundation (Tenants, Plans, Roles, Permissions)...');
        $this->call([
            TenantSeeder::class,
            PlanSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            
        ]);
    $this->call(CategorySeeder::class);

        // Create Super Admin user (platform owner)
        $this->command->info('👤 Creating Super Admin user...');
        $superAdminUserId = (string) \Illuminate\Support\Str::ulid();
        \Illuminate\Support\Facades\DB::table('users')->insert([
            'id' => $superAdminUserId,
            'tenant_id' => null, // Super admin is not tied to a specific tenant
            'name' => 'Super Admin',
            'email' => 'superadmin@smartkodes.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Tenant Admin user for default tenant
        $tenantId = \Illuminate\Support\Facades\DB::table('tenants')->where('slug', 'default')->value('id');
        $tenantAdminRoleId = \Illuminate\Support\Facades\DB::table('roles')->where('slug', 'tenant_admin')->value('id');

        $tenantAdminUserId = (string) \Illuminate\Support\Str::ulid();
        \Illuminate\Support\Facades\DB::table('users')->insert([
            'id' => $tenantAdminUserId,
            'tenant_id' => $tenantId,
            'name' => 'Tenant Admin',
            'email' => 'admin@default.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign Super Admin role to super admin user (assign to default tenant for access)
        $superAdminRoleId = \Illuminate\Support\Facades\DB::table('roles')->where('slug', 'super_admin')->value('id');
        $defaultTenantId = \Illuminate\Support\Facades\DB::table('tenants')->where('slug', 'default')->value('id');
        \Illuminate\Support\Facades\DB::table('role_user')->insert([
            'tenant_id' => $defaultTenantId,
            'role_id' => $superAdminRoleId,
            'user_id' => $superAdminUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Phase 2: Users - Create additional users with various roles
        $this->command->info('👥 Phase 2: Creating users...');
        $this->call([
            UserSeeder::class,
        ]);

        // Phase 3: Projects and Forms
        $this->command->info('📋 Phase 3: Setting up projects and forms...');
        $this->call([
            ProjectSeeder::class,
            FormSeeder::class,
        ]);

        // Phase 4: Work Orders
        $this->command->info('🔨 Phase 4: Creating work orders...');
        $this->call([
            WorkOrderSeeder::class,
        ]);

        // Phase 5: Records with related data
        $this->command->info('📝 Phase 5: Creating records (with comments, activities, approvals)...');
        $this->call([
            RecordSeeder::class,
        ]);

        // Phase 6: Files and Form Submissions
        $this->command->info('📎 Phase 6: Creating files and form submissions...');
        $this->call([
            FileSeeder::class,
            FormSubmissionSeeder::class,
        ]);

        // Phase 7: Notifications
        $this->command->info('🔔 Phase 7: Creating notifications...');
        $this->call([
            NotificationSeeder::class,
        ]);

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->newLine();
        $this->command->info('📊 Default Login Credentials:');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Super Admin', 'superadmin@smartkodes.com', 'password'],
                ['Tenant Admin', 'admin@default.com', 'password'],
                ['Manager', 'manager1@default.com', 'password'],
                ['Field Worker', 'worker1@default.com', 'password'],
            ]
        );
    }
}
