<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class RecordComment extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'record_id',
        'user_id',
        'parent_id',
        'comment',
        'mentions',
        'attachments',
        'is_internal',
    ];

    protected $casts = [
        'mentions' => 'array',
        'attachments' => 'array',
        'is_internal' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the comment.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the record that owns the comment.
     */
    public function record()
    {
        return $this->belongsTo(Record::class);
    }

    /**
     * Get the user who created the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment (for threaded comments).
     */
    public function parent()
    {
        return $this->belongsTo(RecordComment::class, 'parent_id');
    }

    /**
     * Get the child comments (replies).
     */
    public function replies()
    {
        return $this->hasMany(RecordComment::class, 'parent_id');
    }

    /**
     * Get mentioned users.
     */
    public function mentionedUsers()
    {
        if (empty($this->mentions)) {
            return collect();
        }

        return User::whereIn('id', $this->mentions)->get();
    }
}
