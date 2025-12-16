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
        Schema::table('form_fields', function (Blueprint $table) {
            $table->boolean('is_sensitive')->default(false)->after('order');
            $table->string('default_value')->nullable()->after('is_sensitive');
            $table->string('placeholder')->nullable()->after('default_value');
            $table->string('regex_pattern')->nullable()->after('placeholder');
            $table->json('visibility_rules')->nullable()->after('regex_pattern');
            $table->json('conditional_logic')->nullable()->after('visibility_rules');
            $table->decimal('min_value', 15, 2)->nullable()->after('conditional_logic');
            $table->decimal('max_value', 15, 2)->nullable()->after('min_value');
            $table->json('options')->nullable()->after('max_value');
            $table->string('currency_symbol')->default('$')->after('options');
            $table->string('calculation_formula')->nullable()->after('currency_symbol');
            $table->index(['tenant_id', 'is_sensitive']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'is_sensitive']);
            $table->dropColumn([
                'is_sensitive',
                'default_value',
                'placeholder',
                'regex_pattern',
                'visibility_rules',
                'conditional_logic',
                'min_value',
                'max_value',
                'options',
                'currency_symbol',
                'calculation_formula'
            ]);
        });
    }
};
