<?php

namespace App\Models;

use App\Enums\JobType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class JobPosting extends Model
{
    use HasTranslations, SoftDeletes;

    /** @var list<string> */
    public array $translatable = ['title', 'slug', 'description'];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'image',
        'type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'title'       => 'array',
            'slug'        => 'array',
            'description' => 'array',
            'type'        => JobType::class,
            'is_active'   => 'boolean',
        ];
    }

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    // ─────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    /**
     * Get the slug for the given or current locale.
     */
    public function getSlug(?string $locale = null): string
    {
        $loc = $locale ?? app()->getLocale();
        $slug = $this->getTranslation('slug', $loc, false);

        if (! empty($slug)) {
            return $slug;
        }

        // Fallback to EN or ID or string ID
        $fallback = $this->getTranslation('slug', 'en', false)
            ?: $this->getTranslation('slug', 'id', false);

        return ! empty($fallback) ? $fallback : (string) $this->id;
    }

    /**
     * Generate a unique slug for a given locale by appending a numeric suffix
     * if the slug is already taken by another job posting.
     */
    public static function generateUniqueSlug(string $base, string $locale, ?int $excludeId = null): string
    {
        $slug = \Illuminate\Support\Str::slug(trim($base));
        if (empty($slug)) {
            $slug = 'career';
        }

        $original = $slug;
        $suffix   = 1;

        while (true) {
            $exists = static::whereJsonContains('slug->' . $locale, $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists();

            if (! $exists) {
                return $slug;
            }

            $slug = $original . '-' . $suffix;
            $suffix++;
        }
    }
}
