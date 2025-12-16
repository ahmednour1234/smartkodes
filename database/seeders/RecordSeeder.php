<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\WorkOrder;
use App\Models\Record;
use App\Models\RecordField;
use App\Models\RecordComment;
use App\Models\RecordActivity;
use App\Models\RecordApproval;
use App\Models\Form;
use App\Models\FormField;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RecordSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            $workOrders = WorkOrder::where('tenant_id', $tenant->id)
                ->with('forms')
                ->get();
            $users = User::where('tenant_id', $tenant->id)->get();
            
            if ($workOrders->isEmpty() || $users->isEmpty()) {
                continue;
            }
            
            // Create 1-3 records per work order
            foreach ($workOrders as $workOrder) {
                $recordCount = rand(1, 3);
                
                for ($i = 1; $i <= $recordCount; $i++) {
                    $creator = $users->random();
                    
                    // Pick a random form from the work order's forms
                    $form = $workOrder->forms->random();
                    
                    // Determine record status
                    $statusWeights = [
                        'draft' => 15,
                        'submitted' => 30,
                        'under_review' => 25,
                        'approved' => 20,
                        'rejected' => 5,
                        'completed' => 5,
                    ];
                    $status = $this->weightedRandom($statusWeights);
                    
                    // Map status to numeric value (assuming: 0=draft, 1=submitted, 2=in_progress, 3=completed)
                    $statusMap = [
                        'draft' => 0,
                        'submitted' => 1,
                        'under_review' => 1,
                        'approved' => 3,
                        'rejected' => 0,
                        'completed' => 3,
                    ];
                    
                    // Get or create a form version for this form
                    $formVersion = \App\Models\FormVersion::firstOrCreate(
                        [
                            'form_id' => $form->id,
                            'version' => 1,
                        ],
                        [
                            'tenant_id' => $tenant->id,
                            'schema_json' => $form->schema_json,
                            'created_by' => $creator->id,
                        ]
                    );
                    
                    // Create the record
                    $record = Record::create([
                        'tenant_id' => $tenant->id,
                        'work_order_id' => $workOrder->id,
                        'form_id' => $form->id,
                        'form_version' => 1,
                        'form_version_id' => $formVersion->id,
                        'project_id' => $workOrder->project_id,
                        'status' => $statusMap[$status] ?? 0,
                        'submitted_at' => in_array($status, ['submitted', 'under_review', 'approved', 'rejected', 'completed']) 
                            ? now()->subDays(rand(1, 30)) 
                            : null,
                        'submitted_by' => $creator->id,
                        'location' => json_encode([
                            'latitude' => rand(3000, 4500) / 100,
                            'longitude' => rand(-12000, -7000) / 100,
                        ]),
                        'ip_address' => rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255),
                        'created_by' => $creator->id,
                        'updated_by' => $creator->id,
                    ]);
                    
                    // Create record fields based on form fields
                    $this->createRecordFields($record, $form);
                    
                    // Add comments for some records
                    if (rand(0, 1)) {
                        $this->createRecordComments($record, $users);
                    }
                    
                    // Add activities
                    $this->createRecordActivities($record, $users);
                    
                    // Add approval if needed (randomly for some records)
                    if (rand(0, 1) && in_array($status, ['under_review', 'approved', 'rejected'])) {
                        $this->createRecordApproval($record, $users, $status);
                    }
                }
            }
        }
    }
    
    private function createRecordFields(Record $record, Form $form): void
    {
        $formFields = FormField::where('form_id', $form->id)->get();
        
        foreach ($formFields as $formField) {
            $value = $this->generateFieldValue($formField->type);
            
            RecordField::create([
                'tenant_id' => $record->tenant_id,
                'record_id' => $record->id,
                'form_field_id' => $formField->id,
                'value_json' => json_encode(['value' => $value]),
            ]);
        }
    }
    
    private function generateFieldValue(string $fieldType): string
    {
        return match($fieldType) {
            'text' => 'Sample text value ' . rand(1, 100),
            'textarea' => 'This is a longer text value with more details. ' . str_repeat('Lorem ipsum dolor sit amet. ', rand(2, 5)),
            'number' => (string) rand(1, 1000),
            'email' => 'user' . rand(1, 999) . '@example.com',
            'date' => now()->subDays(rand(0, 30))->format('Y-m-d'),
            'time' => sprintf('%02d:%02d', rand(0, 23), rand(0, 59)),
            'datetime' => now()->subDays(rand(0, 30))->format('Y-m-d H:i:s'),
            'select' => ['Option A', 'Option B', 'Option C'][rand(0, 2)],
            'radio' => ['Yes', 'No', 'Maybe'][rand(0, 2)],
            'checkbox' => rand(0, 1) ? '1' : '0',
            'file' => 'uploads/file_' . Str::random(10) . '.pdf',
            'signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUg...',
            'gps' => json_encode(['lat' => rand(3000, 4500) / 100, 'lng' => rand(-12000, -7000) / 100]),
            default => 'Default value',
        };
    }
    
    private function createRecordComments(Record $record, $users): void
    {
        $commentCount = rand(1, 4);
        
        for ($i = 0; $i < $commentCount; $i++) {
            $commenter = $users->random();
            
            RecordComment::create([
                'tenant_id' => $record->tenant_id,
                'record_id' => $record->id,
                'user_id' => $commenter->id,
                'comment' => $this->generateComment(),
                'is_internal' => rand(0, 1),
            ]);
        }
    }
    
    private function generateComment(): string
    {
        $comments = [
            'Please verify the measurements before final submission.',
            'Great work on this inspection!',
            'Need to follow up on the safety concerns mentioned.',
            'Equipment serial number needs to be double-checked.',
            'All requirements have been met.',
            'Consider adding more photos for documentation.',
            'Timeline looks good, approved.',
            'Please provide additional details on the corrective actions.',
        ];
        
        return $comments[array_rand($comments)];
    }
    
    private function createRecordActivities(Record $record, $users): void
    {
        $actions = ['created', 'updated', 'submitted', 'reviewed', 'approved', 'rejected', 'commented'];
        $activityCount = rand(2, 5);
        
        for ($i = 0; $i < $activityCount; $i++) {
            $actor = $users->random();
            $action = $actions[array_rand($actions)];
            
            RecordActivity::create([
                'tenant_id' => $record->tenant_id,
                'record_id' => $record->id,
                'user_id' => $actor->id,
                'action' => $action,
                'description' => $this->generateActivityDescription($action, $actor->name),
                'metadata' => json_encode([
                    'timestamp' => now()->subDays(rand(0, 10))->toIso8601String(),
                    'ip_address' => rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255),
                ]),
            ]);
        }
    }
    
    private function generateActivityDescription(string $activityType, string $userName): string
    {
        return match($activityType) {
            'created' => "{$userName} created this record",
            'updated' => "{$userName} updated the record",
            'submitted' => "{$userName} submitted the record for review",
            'reviewed' => "{$userName} reviewed the record",
            'approved' => "{$userName} approved the record",
            'rejected' => "{$userName} rejected the record",
            'commented' => "{$userName} added a comment",
            default => "{$userName} performed an action on this record",
        };
    }
    
    private function createRecordApproval(Record $record, $users, $status): void
    {
        $approver = $users->random();
        
        $approvalStatus = match($status) {
            'approved' => 'approved',
            'rejected' => 'rejected',
            default => 'pending',
        };
        
        RecordApproval::create([
            'tenant_id' => $record->tenant_id,
            'record_id' => $record->id,
            'approver_id' => $approver->id,
            'status' => $approvalStatus,
            'comments' => $approvalStatus === 'approved' 
                ? 'Approved - All requirements met.'
                : ($approvalStatus === 'rejected' 
                    ? 'Rejected - Please address the identified issues.'
                    : null),
            'approved_at' => $approvalStatus !== 'pending' ? now()->subDays(rand(0, 10)) : null,
        ]);
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
