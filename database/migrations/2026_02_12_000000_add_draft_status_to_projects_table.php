<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }
        $name = DB::selectOne("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'projects' AND CONSTRAINT_TYPE = 'CHECK' LIMIT 1");
        if ($name) {
            DB::statement("ALTER TABLE projects DROP CHECK " . $name->CONSTRAINT_NAME);
        }
        DB::statement("ALTER TABLE projects ADD CONSTRAINT projects_status_check CHECK (status IN (0,1,2,3))");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }
        DB::statement("ALTER TABLE projects DROP CHECK projects_status_check");
        DB::statement("ALTER TABLE projects ADD CONSTRAINT projects_status_check CHECK (status IN (0,1,2))");
    }
};
