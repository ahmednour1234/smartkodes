<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'description',
        'start_date',
        'end_date',
        'geofence',
        'area',
        'client_name',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'geofence' => 'array',
            // 'area' and 'client_name' are strings (no cast needed)
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

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function records(): HasManyThrough
    {
        return $this->hasManyThrough(Record::class, WorkOrder::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
                    ->using(ProjectUser::class)
                    ->withPivot('role', 'assigned_by')
                    ->withTimestamps();
    }

    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
                    ->using(ProjectUser::class)
                    ->wherePivot('role', 'manager')
                    ->withPivot('assigned_by')
                    ->withTimestamps();
    }

    public function fieldUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
                    ->using(ProjectUser::class)
                    ->wherePivot('role', 'field_user')
                    ->withPivot('assigned_by')
                    ->withTimestamps();
    }
}
