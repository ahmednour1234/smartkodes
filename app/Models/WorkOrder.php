<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'title',
        'project_id',
        'assigned_to',
        'status',
        'importance_level',
        'due_date',
        'created_by',
        'updated_by',
        'priority_value',
        'priority_unit',
        'latitude',
        'longitude',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'due_date' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
       public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function forms(): BelongsToMany
    {
        return $this->belongsToMany(Form::class, 'form_work_order')
                    ->withTimestamps()
                    ->withPivot(['id', 'order'])
                    ->orderByPivot('order', 'asc');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function records(): HasMany
    {
        return $this->hasMany(Record::class);
    }
}
