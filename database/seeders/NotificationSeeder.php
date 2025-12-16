<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Notification;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Record;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            $users = User::where('tenant_id', $tenant->id)->get();
            $workOrders = WorkOrder::where('tenant_id', $tenant->id)->get();
            $records = Record::where('tenant_id', $tenant->id)->get();
            
            if ($users->isEmpty()) {
                continue;
            }
            
            // Create various types of notifications for each user
            foreach ($users as $user) {
                // Work order assignment notifications
                if ($workOrders->isNotEmpty()) {
                    $assignedWorkOrders = $workOrders->where('assigned_to', $user->id)->take(rand(1, 3));
                    
                    foreach ($assignedWorkOrders as $workOrder) {
                        Notification::create([
                            'tenant_id' => $tenant->id,
                            'user_id' => $user->id,
                            'type' => 'work_order_assigned',
                            'title' => 'New Work Order Assigned',
                            'message' => "You have been assigned to work order for project: {$workOrder->project->name}",
                            'data' => json_encode([
                                'work_order_id' => $workOrder->id,
                                'project_id' => $workOrder->project_id,
                                'due_date' => $workOrder->due_date,
                            ]),
                            'read_at' => rand(0, 1) ? now()->subDays(rand(1, 10)) : null,
                        ]);
                    }
                }
                
                // Record submission notifications
                if ($records->isNotEmpty() && rand(0, 1)) {
                    $randomRecords = $records->random(min(2, $records->count()));
                    
                    foreach ($randomRecords as $record) {
                        Notification::create([
                            'tenant_id' => $tenant->id,
                            'user_id' => $user->id,
                            'type' => 'record_submitted',
                            'title' => 'New Record Submitted',
                            'message' => "A new record has been submitted for review",
                            'data' => json_encode([
                                'record_id' => $record->id,
                                'form_name' => $record->form->name,
                                'submitted_by' => $record->created_by,
                            ]),
                            'read_at' => rand(0, 1) ? now()->subDays(rand(1, 5)) : null,
                        ]);
                    }
                }
                
                // Approval notifications
                if (rand(0, 1)) {
                    Notification::create([
                        'tenant_id' => $tenant->id,
                        'user_id' => $user->id,
                        'type' => 'approval_required',
                        'title' => 'Approval Required',
                        'message' => 'A record requires your approval',
                        'data' => json_encode([
                            'record_id' => $records->isNotEmpty() ? $records->random()->id : null,
                            'priority' => 'high',
                        ]),
                        'read_at' => rand(0, 1) ? now()->subDays(rand(0, 3)) : null,
                    ]);
                }
                
                // Due date reminder notifications
                if (rand(0, 1)) {
                    Notification::create([
                        'tenant_id' => $tenant->id,
                        'user_id' => $user->id,
                        'type' => 'due_date_reminder',
                        'title' => 'Work Order Due Soon',
                        'message' => 'You have a work order due in 2 days',
                        'data' => json_encode([
                            'work_order_id' => $workOrders->isNotEmpty() ? $workOrders->random()->id : null,
                            'due_date' => now()->addDays(2)->format('Y-m-d'),
                        ]),
                        'read_at' => null,
                    ]);
                }
                
                // Comment notifications
                if (rand(0, 1)) {
                    Notification::create([
                        'tenant_id' => $tenant->id,
                        'user_id' => $user->id,
                        'type' => 'new_comment',
                        'title' => 'New Comment on Your Record',
                        'message' => 'Someone commented on your record submission',
                        'data' => json_encode([
                            'record_id' => $records->isNotEmpty() ? $records->random()->id : null,
                            'commenter' => $users->random()->name,
                        ]),
                        'read_at' => rand(0, 1) ? now()->subDays(rand(0, 2)) : null,
                    ]);
                }
                
                // System notifications
                if (rand(0, 1)) {
                    $systemNotifications = [
                        [
                            'title' => 'System Maintenance Scheduled',
                            'message' => 'System maintenance is scheduled for this weekend',
                        ],
                        [
                            'title' => 'New Feature Available',
                            'message' => 'Check out the new GPS tracking feature',
                        ],
                        [
                            'title' => 'Monthly Report Ready',
                            'message' => 'Your monthly activity report is now available',
                        ],
                    ];
                    
                    $systemNotif = $systemNotifications[array_rand($systemNotifications)];
                    
                    Notification::create([
                        'tenant_id' => $tenant->id,
                        'user_id' => $user->id,
                        'type' => 'system',
                        'title' => $systemNotif['title'],
                        'message' => $systemNotif['message'],
                        'data' => json_encode([
                            'category' => 'system',
                            'priority' => 'low',
                        ]),
                        'read_at' => rand(0, 1) ? now()->subDays(rand(0, 7)) : null,
                    ]);
                }
            }
        }
    }
}
