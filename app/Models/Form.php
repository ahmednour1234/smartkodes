<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'schema_json',
        'version',
        'status',
        'created_by',
        'updated_by',
        'category_id'
    ];

    protected function casts(): array
    {
        return [
            'schema_json' => 'array',
            'version' => 'integer',
            'status' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(FormField::class);
    }

    public function formVersions(): HasMany
    {
        return $this->hasMany(FormVersion::class);
    }

    public function workOrders(): BelongsToMany
    {
        return $this->belongsToMany(WorkOrder::class, 'form_work_order')
                    ->withTimestamps()
                    ->withPivot(['id', 'order'])
                    ->orderByPivot('order', 'asc');
    }
    public function category()
{
    return $this->belongsTo(Category::class);
}
}
