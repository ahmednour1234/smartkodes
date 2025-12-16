<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('personal_access_tokens')) {
            return;
        }

        // Use raw SQL to alter column length to 26 characters for ULIDs.
        // Works on MySQL/MariaDB; safe if already 26.
        try {
            DB::statement('ALTER TABLE personal_access_tokens MODIFY tokenable_id VARCHAR(26)');
        } catch (\Throwable $e) {
            // Ignore if change not needed or fails due to no-op
        }
    }

    public function down(): void
    {
        // No down migration to avoid truncation risk.
    }
};
