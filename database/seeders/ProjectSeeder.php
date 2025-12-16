<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $tenants = Tenant::all();
        
        $projectTypes = [
            'Construction',
            'Inspection',
            'Maintenance',
            'Survey',
            'Installation',
            'Repair',
            'Assessment',
        ];
        
        $areas = [
            'Downtown',
            'North District',
            'South District',
            'East Side',
            'West Side',
            'Industrial Zone',
            'Residential Area',
            'Commercial District',
        ];
        
        foreach ($tenants as $tenant) {
            $users = User::where('tenant_id', $tenant->id)->get();
            
            if ($users->isEmpty()) {
                continue;
            }
            
            // Create 5-8 projects per tenant
            $projectCount = rand(5, 8);
            
            for ($i = 1; $i <= $projectCount; $i++) {
                $projectType = $projectTypes[array_rand($projectTypes)];
                $area = $areas[array_rand($areas)];
                $creator = $users->random();
                
                // Generate a random project code
                $code = 'PRJ-' . strtoupper(substr($tenant->slug, 0, 3)) . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);
                
                $project = Project::create([
                    'tenant_id' => $tenant->id,
                    'name' => "{$projectType} - {$area}",
                    'description' => "This is a {$projectType} project in the {$area} for {$tenant->name}. " .
                                   "The project involves comprehensive {$projectType} activities and requires field data collection.",
                    'code' => $code,
                    'area' => $area,
                    'client_name' => "Client " . chr(65 + rand(0, 25)) . chr(65 + rand(0, 25)),
                    'start_date' => now()->subDays(rand(30, 180)),
                    'end_date' => now()->addDays(rand(30, 180)),
                    'geofence' => json_encode([
                        'enabled' => rand(0, 1),
                        'radius' => rand(50, 500),
                        'latitude' => rand(3000, 4500) / 100,
                        'longitude' => rand(-12000, -7000) / 100,
                    ]),
                    'status' => rand(0, 2), // 0=active, 1=completed, 2=archived
                    'created_by' => $creator->id,
                    'updated_by' => $creator->id,
                ]);
                
                // Assign 1-3 random users to the project
                $assignedUsers = $users->random(rand(1, min(3, $users->count())));
                foreach ($assignedUsers as $user) {
                    $project->members()->attach($user->id, [
                        'id' => Str::ulid(),
                    ]);
                }
            }
        }
    }
}
