<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Database\Seeder;

class FormSubmissionSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            $forms = Form::where('tenant_id', $tenant->id)->get();
            $users = User::where('tenant_id', $tenant->id)->get();
            
            if ($forms->isEmpty() || $users->isEmpty()) {
                continue;
            }
            
            // Create 5-10 standalone form submissions per tenant
            $submissionCount = rand(5, 10);
            
            for ($i = 0; $i < $submissionCount; $i++) {
                $form = $forms->random();
                $user = $users->random();
                $reviewer = $users->random();
                
                // Determine status
                $statusWeights = [
                    'draft' => 10,
                    'submitted' => 30,
                    'pending_review' => 25,
                    'approved' => 20,
                    'rejected' => 10,
                    'completed' => 5,
                ];
                $status = $this->weightedRandom($statusWeights);
                
                // Generate form data
                $formData = $this->generateFormData($form);
                
                \App\Models\FormSubmission::create([
                    'form_id' => $form->id,
                    'user_id' => $user->id,
                    'tenant_id' => $tenant->id,
                    'data' => json_encode($formData),
                    'status' => $status,
                    'submitted_at' => in_array($status, ['submitted', 'pending_review', 'approved', 'rejected', 'completed'])
                        ? now()->subDays(rand(1, 30))
                        : null,
                    'reviewed_at' => in_array($status, ['approved', 'rejected', 'completed'])
                        ? now()->subDays(rand(0, 15))
                        : null,
                    'reviewed_by' => in_array($status, ['approved', 'rejected', 'completed'])
                        ? $reviewer->id
                        : null,
                    'notes' => $this->generateNotes($status),
                ]);
            }
        }
    }
    
    private function generateFormData(Form $form): array
    {
        $formData = [];
        $fields = $form->formFields;
        
        foreach ($fields as $field) {
            $formData[$field->name] = $this->generateFieldValue($field->type);
        }
        
        return $formData;
    }
    
    private function generateFieldValue(string $fieldType): mixed
    {
        return match($fieldType) {
            'text' => 'Sample text ' . rand(1, 100),
            'textarea' => 'This is a detailed response with multiple lines. ' . str_repeat('Additional information provided here. ', rand(2, 4)),
            'number' => rand(1, 1000),
            'email' => 'user' . rand(1, 999) . '@example.com',
            'date' => now()->subDays(rand(0, 30))->format('Y-m-d'),
            'time' => sprintf('%02d:%02d', rand(0, 23), rand(0, 59)),
            'datetime' => now()->subDays(rand(0, 30))->format('Y-m-d H:i:s'),
            'select' => ['Option A', 'Option B', 'Option C', 'Option D'][rand(0, 3)],
            'radio' => ['Yes', 'No'][rand(0, 1)],
            'checkbox' => rand(0, 1) ? true : false,
            'file' => 'uploads/submission_' . rand(1000, 9999) . '.pdf',
            'signature' => 'data:image/png;base64,signature_data_here',
            'gps' => [
                'latitude' => rand(3000, 4500) / 100,
                'longitude' => rand(-12000, -7000) / 100,
            ],
            default => 'Default value',
        };
    }
    
    private function generateNotes(string $status): ?string
    {
        return match($status) {
            'approved' => 'Submission approved. All information is complete and accurate.',
            'rejected' => 'Submission rejected. Please review and resubmit with corrections.',
            'pending_review' => 'Submission is under review by the approval team.',
            'completed' => 'Submission processed and completed successfully.',
            default => null,
        };
    }
    
    /**
     * Select a random item based on weights
     */
    private function weightedRandom(array $weights): string
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
