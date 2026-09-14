<?php

namespace App\Models;

use Database\Factories\ArticleCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Spatie\Translatable\HasTranslations;

class ArticleCategory extends Model
{
    /** @use HasFactory<ArticleCategoryFactory> */
    use HasFactory, HasTranslations;

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('sitemap_xml');
        });

        static::deleted(function () {
            Cache::forget('sitemap_xml');
        });
    }

    /**
     * Columns that are translatable JSON fields.
     *
     * @var list<string>
     */
    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'color',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
        ];
    }

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ArticleCategory::class, 'parent_id');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    // ─────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────

    /** Only top-level (root) categories. */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
}
