<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class RecordApproval extends Model
{
    use HasUlids;

    protected $fillable = [
        'tenant_id',
        'record_id',
        'approver_id',
        'requested_by',
        'sequence',
        'status',
        'comments',
        'approved_at',
        'rejected_at',
        'delegated_to',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the approval.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the record that owns the approval.
     */
    public function record()
    {
        return $this->belongsTo(Record::class);
    }

    /**
     * Get the approver user.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Get the user who requested approval.
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user to whom approval was delegated.
     */
    public function delegatedUser()
    {
        return $this->belongsTo(User::class, 'delegated_to');
    }

    /**
     * Check if approval is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if approval is approved.
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if approval is rejected.
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }
}
