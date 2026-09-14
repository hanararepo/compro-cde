<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Award extends Model
{
    use HasTranslations, SoftDeletes;

    /**
     * Columns translatable via JSON (spatie/laravel-translatable).
     *
     * @var list<string>
     */
    public array $translatable = ['title', 'description'];

    protected $fillable = [
        'title',
        'description',
        'image_path',
        'type',
        'issued_date',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'title'       => 'array',
            'description' => 'array',
            'issued_date' => 'date',
            'is_active'   => 'boolean',
        ];
    }

    // ─────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────

    /** Only active awards (public frontend). */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Filter by type: 'award' | 'certificate'. */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    public function imageUrl(): string
    {
        return asset('storage/' . $this->image_path);
    }

    public function isAward(): bool
    {
        return $this->type === 'award';
    }

    public function isCertificate(): bool
    {
        return $this->type === 'certificate';
    }
}
