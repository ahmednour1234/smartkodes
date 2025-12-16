<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormField extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'form_id',
        'name',
        'type',
        'config_json',
        'order',
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
        'calculation_formula',
    ];

    protected function casts(): array
    {
        return [
            'config_json' => 'array',
            'order' => 'integer',
            'is_sensitive' => 'boolean',
            'visibility_rules' => 'array',
            'conditional_logic' => 'array',
            'min_value' => 'decimal:2',
            'max_value' => 'decimal:2',
            'options' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function recordFields(): HasMany
    {
        return $this->hasMany(RecordField::class);
    }
}
