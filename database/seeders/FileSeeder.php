<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\File;
use App\Models\Record;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            $records = Record::where('tenant_id', $tenant->id)->get();
            $users = User::where('tenant_id', $tenant->id)->get();
            
            if ($records->isEmpty() || $users->isEmpty()) {
                continue;
            }
            
            // Create 1-4 files per record
            foreach ($records as $record) {
                $fileCount = rand(1, 4);
                
                for ($i = 0; $i < $fileCount; $i++) {
                    $uploader = $users->random();
                    $fileType = $this->getRandomFileType();
                    
                    File::create([
                        'tenant_id' => $tenant->id,
                        'record_id' => $record->id,
                        'name' => $this->generateOriginalFilename($fileType),
                        'path' => "uploads/tenant_{$tenant->id}/records/{$record->id}/" . Str::random(40) . '.' . $fileType['extension'],
                        'type' => $fileType['extension'],
                        'mime_type' => $fileType['mime'],
                        'size' => rand(10240, 10485760), // 10KB to 10MB
                        'created_by' => $uploader->id,
                    ]);
                }
            }
            
            // Also create some standalone files not attached to records
            $standaloneCount = rand(5, 10);
            for ($i = 0; $i < $standaloneCount; $i++) {
                $uploader = $users->random();
                $fileType = $this->getRandomFileType();
                
                File::create([
                    'tenant_id' => $tenant->id,
                    'record_id' => null,
                    'name' => $this->generateOriginalFilename($fileType),
                    'path' => "uploads/tenant_{$tenant->id}/misc/" . Str::random(40) . '.' . $fileType['extension'],
                    'type' => $fileType['extension'],
                    'mime_type' => $fileType['mime'],
                    'size' => rand(10240, 10485760),
                    'created_by' => $uploader->id,
                ]);
            }
        }
    }
    
    private function getRandomFileType(): array
    {
        $fileTypes = [
            ['extension' => 'jpg', 'mime' => 'image/jpeg'],
            ['extension' => 'png', 'mime' => 'image/png'],
            ['extension' => 'pdf', 'mime' => 'application/pdf'],
            ['extension' => 'docx', 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            ['extension' => 'xlsx', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            ['extension' => 'mp4', 'mime' => 'video/mp4'],
            ['extension' => 'mov', 'mime' => 'video/quicktime'],
            ['extension' => 'txt', 'mime' => 'text/plain'],
            ['extension' => 'csv', 'mime' => 'text/csv'],
        ];
        
        return $fileTypes[array_rand($fileTypes)];
    }
    
    private function generateFilename(array $fileType): string
    {
        return Str::random(32) . '.' . $fileType['extension'];
    }
    
    private function generateOriginalFilename(array $fileType): string
    {
        $prefixes = [
            'inspection',
            'photo',
            'document',
            'report',
            'assessment',
            'measurement',
            'checklist',
            'signature',
            'evidence',
            'survey',
        ];
        
        $prefix = $prefixes[array_rand($prefixes)];
        return $prefix . '_' . date('Ymd') . '_' . rand(1000, 9999) . '.' . $fileType['extension'];
    }
}
