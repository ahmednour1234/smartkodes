<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class RecordActivity extends Model
{
    use HasUlids;

    const UPDATED_AT = null; // Only created_at timestamp

    protected $fillable = [
        'tenant_id',
        'record_id',
        'user_id',
        'action',
        'field_name',
        'old_value',
        'new_value',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the activity.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the record that owns the activity.
     */
    public function record()
    {
        return $this->belongsTo(Record::class);
    }

    /**
     * Get the user who performed the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get human-readable action name.
     */
    public function getActionNameAttribute()
    {
        $actions = [
            'created' => 'Created Record',
            'updated' => 'Updated Field',
            'status_changed' => 'Changed Status',
            'assigned' => 'Assigned Record',
            'commented' => 'Added Comment',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'file_uploaded' => 'Uploaded File',
            'file_deleted' => 'Deleted File',
        ];

        return $actions[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }
}
