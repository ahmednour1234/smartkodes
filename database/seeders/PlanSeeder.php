<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'id' => (string) \Illuminate\Support\Str::ulid(),
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for individuals and small teams just getting started.',
                'price' => 19.99,
                'features' => json_encode([
                    'projects' => '3',
                    'users' => '5',
                    'storage' => '500 MB',
                    'forms' => '10',
                    'work_orders' => '50/month',
                    'api_access' => 'No',
                ]),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) \Illuminate\Support\Str::ulid(),
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'For growing businesses that need more power and flexibility.',
                'price' => 49.99,
                'features' => json_encode([
                    'projects' => '10',
                    'users' => '20',
                    'storage' => '5 GB',
                    'forms' => '50',
                    'work_orders' => '200/month',
                    'api_access' => 'Yes',
                ]),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) \Illuminate\Support\Str::ulid(),
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Advanced features for large organizations with complex needs.',
                'price' => 99.99,
                'features' => json_encode([
                    'projects' => 'Unlimited',
                    'users' => '100',
                    'storage' => '50 GB',
                    'forms' => 'Unlimited',
                    'work_orders' => 'Unlimited',
                    'api_access' => 'Yes',
                    'priority_support' => 'Yes',
                    'custom_branding' => 'Yes',
                ]),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($plans as $plan) {
            \Illuminate\Support\Facades\DB::table('plans')->insert($plan);
        }
    }
}
