<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Form;
use App\Models\FormField;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FormSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            $users = User::where('tenant_id', $tenant->id)->get();
            
            if ($users->isEmpty()) {
                continue;
            }
            
            $creator = $users->first();
            
            // 1. Inspection Form
            $inspectionForm = Form::create([
                'tenant_id' => $tenant->id,
                'name' => 'Site Inspection Form',
                'description' => 'Comprehensive site inspection checklist for field workers',
                'schema_json' => json_encode([
                    'title' => 'Site Inspection',
                    'description' => 'Complete this form during site inspections',
                ]),
                'version' => 1,
                'status' => 1, // Published
                'created_by' => $creator->id,
                'updated_by' => $creator->id,
            ]);
            
            // Add fields to inspection form
            $this->createInspectionFormFields($inspectionForm);
            
            // 2. Safety Assessment Form
            $safetyForm = Form::create([
                'tenant_id' => $tenant->id,
                'name' => 'Safety Assessment Form',
                'description' => 'Safety compliance and risk assessment form',
                'schema_json' => json_encode([
                    'title' => 'Safety Assessment',
                    'description' => 'Evaluate safety conditions and compliance',
                ]),
                'version' => 1,
                'status' => 1,
                'created_by' => $creator->id,
                'updated_by' => $creator->id,
            ]);
            
            $this->createSafetyFormFields($safetyForm);
            
            // 3. Equipment Maintenance Form
            $maintenanceForm = Form::create([
                'tenant_id' => $tenant->id,
                'name' => 'Equipment Maintenance Log',
                'description' => 'Record equipment maintenance activities and findings',
                'schema_json' => json_encode([
                    'title' => 'Equipment Maintenance',
                    'description' => 'Document all maintenance work performed',
                ]),
                'version' => 1,
                'status' => 1,
                'created_by' => $creator->id,
                'updated_by' => $creator->id,
            ]);
            
            $this->createMaintenanceFormFields($maintenanceForm);
            
            // 4. Work Order Completion Form
            $completionForm = Form::create([
                'tenant_id' => $tenant->id,
                'name' => 'Work Order Completion Form',
                'description' => 'Final report for completed work orders',
                'schema_json' => json_encode([
                    'title' => 'Work Order Completion',
                    'description' => 'Submit completion details and sign-off',
                ]),
                'version' => 1,
                'status' => 1,
                'created_by' => $creator->id,
                'updated_by' => $creator->id,
            ]);
            
            $this->createCompletionFormFields($completionForm);
            
            // 5. Survey Form
            $surveyForm = Form::create([
                'tenant_id' => $tenant->id,
                'name' => 'Field Survey Form',
                'description' => 'Data collection form for field surveys',
                'schema_json' => json_encode([
                    'title' => 'Field Survey',
                    'description' => 'Collect survey data and measurements',
                ]),
                'version' => 1,
                'status' => 1,
                'created_by' => $creator->id,
                'updated_by' => $creator->id,
            ]);
            
            $this->createSurveyFormFields($surveyForm);
        }
    }
    
    private function createInspectionFormFields(Form $form): void
    {
        $fields = [
            [
                'name' => 'inspector_name',
                'type' => 'text',
                'order' => 1,
                'config_json' => json_encode(['label' => 'Inspector Name', 'required' => true]),
            ],
            [
                'name' => 'inspection_date',
                'type' => 'date',
                'order' => 2,
                'config_json' => json_encode(['label' => 'Inspection Date', 'required' => true]),
            ],
            [
                'name' => 'location',
                'type' => 'text',
                'order' => 3,
                'config_json' => json_encode(['label' => 'Location', 'required' => true]),
            ],
            [
                'name' => 'condition',
                'type' => 'select',
                'order' => 4,
                'options' => json_encode(['Excellent', 'Good', 'Fair', 'Poor', 'Critical']),
                'config_json' => json_encode(['label' => 'Overall Condition', 'required' => true]),
            ],
            [
                'name' => 'issues_found',
                'type' => 'textarea',
                'order' => 5,
                'config_json' => json_encode(['label' => 'Issues Found', 'required' => false]),
            ],
            [
                'name' => 'photos',
                'type' => 'file',
                'order' => 6,
                'config_json' => json_encode(['label' => 'Inspection Photos', 'required' => false]),
            ],
            [
                'name' => 'requires_action',
                'type' => 'checkbox',
                'order' => 7,
                'config_json' => json_encode(['label' => 'Requires Immediate Action', 'required' => false]),
            ],
            [
                'name' => 'notes',
                'type' => 'textarea',
                'order' => 8,
                'config_json' => json_encode(['label' => 'Additional Notes', 'required' => false]),
            ],
        ];
        
        foreach ($fields as $fieldData) {
            FormField::create(array_merge($fieldData, [
                'form_id' => $form->id,
                'tenant_id' => $form->tenant_id,
            ]));
        }
    }
    
    private function createSafetyFormFields(Form $form): void
    {
        $this->createFieldsForForm($form, [
            ['assessor_name', 'text', 'Assessor Name', true, 1],
            ['assessment_date', 'date', 'Assessment Date', true, 2],
            ['ppe_compliance', 'select', 'PPE Compliance', true, 3, ['Fully Compliant', 'Partially Compliant', 'Non-Compliant']],
            ['hazards_identified', 'textarea', 'Hazards Identified', false, 4],
            ['risk_level', 'select', 'Risk Level', true, 5, ['Low', 'Medium', 'High', 'Critical']],
            ['corrective_actions', 'textarea', 'Corrective Actions Taken', false, 6],
            ['follow_up_required', 'checkbox', 'Follow-up Required', false, 7],
        ]);
    }
    
    private function createMaintenanceFormFields(Form $form): void
    {
        $this->createFieldsForForm($form, [
            ['equipment_id', 'text', 'Equipment ID', true, 1],
            ['equipment_name', 'text', 'Equipment Name', true, 2],
            ['maintenance_type', 'select', 'Maintenance Type', true, 3, ['Preventive', 'Corrective', 'Emergency', 'Routine']],
            ['maintenance_date', 'date', 'Maintenance Date', true, 4],
            ['hours_spent', 'number', 'Hours Spent', true, 5],
            ['work_performed', 'textarea', 'Work Performed', true, 6],
            ['parts_replaced', 'textarea', 'Parts Replaced', false, 7],
            ['next_maintenance_date', 'date', 'Next Maintenance Date', false, 8],
        ]);
    }
    
    private function createCompletionFormFields(Form $form): void
    {
        $this->createFieldsForForm($form, [
            ['completion_date', 'date', 'Completion Date', true, 1],
            ['work_summary', 'textarea', 'Work Summary', true, 2],
            ['materials_used', 'textarea', 'Materials Used', false, 3],
            ['labor_hours', 'number', 'Total Labor Hours', true, 4],
            ['quality_rating', 'select', 'Quality Rating', true, 5, ['Excellent', 'Good', 'Satisfactory', 'Needs Improvement']],
            ['completion_photos', 'file', 'Completion Photos', false, 6],
            ['signature', 'signature', 'Signature', true, 7],
        ]);
    }
    
    private function createSurveyFormFields(Form $form): void
    {
        $this->createFieldsForForm($form, [
            ['surveyor_name', 'text', 'Surveyor Name', true, 1],
            ['survey_date', 'date', 'Survey Date', true, 2],
            ['gps_coordinates', 'gps', 'GPS Coordinates', true, 3],
            ['measurement_value', 'number', 'Measurement Value', true, 4],
            ['unit_of_measure', 'select', 'Unit of Measure', true, 5, ['meters', 'feet', 'kilometers', 'miles', 'acres', 'hectares']],
            ['observations', 'textarea', 'Field Observations', false, 6],
            ['survey_photos', 'file', 'Survey Photos', false, 7],
        ]);
    }
    
    private function createFieldsForForm(Form $form, array $fieldsDef): void
    {
        foreach ($fieldsDef as $def) {
            [$name, $type, $label, $required, $order, $options] = array_pad($def, 6, null);
            
            $fieldData = [
                'form_id' => $form->id,
                'tenant_id' => $form->tenant_id,
                'name' => $name,
                'type' => $type,
                'order' => $order,
                'config_json' => json_encode(['label' => $label, 'required' => $required]),
            ];
            
            if ($options) {
                $fieldData['options'] = json_encode($options);
            }
            
            FormField::create($fieldData);
        }
    }
}
