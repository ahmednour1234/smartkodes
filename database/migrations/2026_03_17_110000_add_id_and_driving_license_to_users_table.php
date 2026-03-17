<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'id_copy_path')) {
                $table->string('id_copy_path')->nullable()->after('photo_path');
            }

            if (!Schema::hasColumn('users', 'driving_license_path')) {
                $table->string('driving_license_path')->nullable()->after('id_copy_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'driving_license_path')) {
                $table->dropColumn('driving_license_path');
            }

            if (Schema::hasColumn('users', 'id_copy_path')) {
                $table->dropColumn('id_copy_path');
            }
        });
    }
};
