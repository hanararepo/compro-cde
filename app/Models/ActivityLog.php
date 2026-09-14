<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'event',
        'subject_type',
        'subject_id',
        'subject_label',
        'description',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /* ------------------------------------------------------------------ */
    /* Relations */
    /* ------------------------------------------------------------------ */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault(['name' => 'System']);
    }

    /* ------------------------------------------------------------------ */
    /* Scopes */
    /* ------------------------------------------------------------------ */

    public function scopeForEvent(Builder $query, string $event): Builder
    {
        return $query->where('event', $event);
    }

    public function scopeForSubjectType(Builder $query, string $type): Builder
    {
        return $query->where('subject_type', $type);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeFromDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', '>=', $date);
    }

    public function scopeToDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', '<=', $date);
    }

    /* ------------------------------------------------------------------ */
    /* Helpers */
    /* ------------------------------------------------------------------ */

    /**
     * Returns a short human-friendly label for the subject_type class name.
     */
    public function subjectTypeLabel(): string
    {
        if (! $this->subject_type) {
            return '—';
        }

        return class_basename($this->subject_type);
    }

    /**
     * Returns a Tailwind colour token for the event badge.
     */
    public function eventColour(): string
    {
        return match ($this->event) {
            'created' => 'emerald',
            'updated' => 'indigo',
            'deleted' => 'rose',
            'approved' => 'teal',
            'rejected' => 'amber',
            'logged_in' => 'sky',
            'logged_out' => 'slate',
            default => 'slate',
        };
    }
}
