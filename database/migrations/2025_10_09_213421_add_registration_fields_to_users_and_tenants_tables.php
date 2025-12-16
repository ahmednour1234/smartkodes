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
        // Add new fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone')->nullable()->after('email');
            $table->string('country', 2)->nullable()->after('phone');
        });

        // Add new fields to tenants table
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('name');
            $table->string('field_of_work')->nullable()->after('company_name');
            $table->integer('num_users')->default(1)->after('field_of_work');
            $table->decimal('monthly_price', 10, 2)->default(10.00)->after('num_users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'phone', 'country']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'field_of_work', 'num_users', 'monthly_price']);
        });
    }
};
