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
            if (!Schema::hasColumn('users', 'passcode')) {
                $table->string('passcode', 6)->nullable()->after('password');
                $table->timestamp('passcode_set_at')->nullable()->after('passcode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'passcode')) {
                $table->dropColumn('passcode');
            }
            if (Schema::hasColumn('users', 'passcode_set_at')) {
                $table->dropColumn('passcode_set_at');
            }
        });
    }
};

