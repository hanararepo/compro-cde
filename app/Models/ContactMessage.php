<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'is_read',
        'replied_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'replied_at' => 'datetime',
        ];
    }

    // ─────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────

    /** Filter only unread messages. */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }
}
