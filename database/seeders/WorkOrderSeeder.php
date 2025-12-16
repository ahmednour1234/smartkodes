<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Project;
use App\Models\WorkOrder;
use App\Models\Form;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WorkOrderSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            $projects = Project::where('tenant_id', $tenant->id)->get();
            $forms = Form::where('tenant_id', $tenant->id)->get();
            $users = User::where('tenant_id', $tenant->id)->get();
            
            if ($projects->isEmpty() || $forms->isEmpty() || $users->isEmpty()) {
                continue;
            }
            
            // Create 3-5 work orders per project
            foreach ($projects as $project) {
                $workOrderCount = rand(3, 5);
                
                for ($i = 1; $i <= $workOrderCount; $i++) {
                    $creator = $users->random();
                    $assignee = $users->random();
                    
                    // Determine status - mix of different states
                    $statusWeights = [
                        0 => 10, // draft
                        1 => 40, // assigned
                        2 => 30, // in_progress
                        3 => 20, // completed
                    ];
                    $status = $this->weightedRandom($statusWeights);
                    
                    $workOrder = WorkOrder::create([
                        'tenant_id' => $tenant->id,
                        'project_id' => $project->id,
                        'assigned_to' => $assignee->id,
                        'status' => $status,
                        'due_date' => now()->addDays(rand(1, 60)),
                        'created_by' => $creator->id,
                        'updated_by' => $creator->id,
                    ]);
                    
                    // Attach 1-3 random forms with order
                    $formCount = rand(1, min(3, $forms->count()));
                    $selectedForms = $forms->random($formCount);
                    
                    $formData = [];
                    foreach ($selectedForms as $index => $form) {
                        $formData[$form->id] = [
                            'id' => Str::ulid(),
                            'order' => $index,
                        ];
                    }
                    
                    $workOrder->forms()->attach($formData);
                }
            }
        }
    }
    
    /**
     * Select a random item based on weights
     */
    private function weightedRandom(array $weights): int
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($weights as $key => $weight) {
            $currentWeight += $weight;
            if ($random <= $currentWeight) {
                return $key;
            }
        }
        
        return array_key_first($weights);
    }
}
