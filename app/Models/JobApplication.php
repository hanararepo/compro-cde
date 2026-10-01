<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    protected $fillable = [
        'job_posting_id',
        'name',
        'email',
        'phone',
        'linkedin',
        'cv_path',
        'cv_original_name',
        'ip_address',
        'email_status',
        'email_attempts',
        'email_recipient',
        'email_sent_at',
        'email_next_attempt_at',
        'email_last_error',
    ];

    protected function casts(): array
    {
        return [
            'email_attempts' => 'integer',
            'email_sent_at' => 'datetime',
            'email_next_attempt_at' => 'datetime',
        ];
    }

    protected function email(): Attribute
    {
        return Attribute::make(set: fn (string $value) => mb_strtolower(trim($value)));
    }

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }
}
