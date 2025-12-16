<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Webhook extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'url',
        'events',
        'secret',
        'status',
        'last_triggered_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'status' => 'integer',
            'last_triggered_at' => 'datetime',
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

    /**
     * Check if webhook is active.
     */
    public function isActive()
    {
        return $this->status === 1;
    }

    /**
     * Check if webhook should trigger for given event.
     */
    public function shouldTriggerFor(string $event)
    {
        return in_array($event, $this->events ?? []);
    }

    /**
     * Update last triggered timestamp.
     */
    public function markAsTriggered()
    {
        $this->update(['last_triggered_at' => now()]);
    }
}
