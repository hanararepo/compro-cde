<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Spatie\Translatable\HasTranslations;

class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory, HasTranslations, SoftDeletes;

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
     * Columns translatable via JSON (spatie/laravel-translatable).
     *
     * @var list<string>
     */
    public array $translatable = ['title', 'slug', 'content', 'summary'];

    protected $fillable = [
        'title',
        'slug',
        'content',
        'summary',
        'thumbnail',
        'status',
        'rejection_reason',
        'published_at',
        'article_category_id',
        'author_id',
        'approved_by',
        'approved_at',
        'views_count',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'slug' => 'array',
            'content' => 'array',
            'summary' => 'array',
            'status' => ArticleStatus::class,
            'published_at' => 'datetime',
            'approved_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ArticleTag::class, 'article_article_tag');
    }

    // ─────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────

    /** Filter to only published articles (for public frontend). */
    public function scopePublished($query)
    {
        return $query->where('status', ArticleStatus::Published);
    }

    /** Filter to articles pending approval. */
    public function scopePending($query)
    {
        return $query->where('status', ArticleStatus::Pending);
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    public function isPublished(): bool
    {
        return $this->status === ArticleStatus::Published;
    }

    public function isPending(): bool
    {
        return $this->status === ArticleStatus::Pending;
    }

    public function isUnpublished(): bool
    {
        return $this->status === ArticleStatus::Unpublished;
    }

    public function thumbnailUrl(): ?string
    {
        return $this->thumbnail ? asset('storage/'.$this->thumbnail) : null;
    }

    public function publicUrl(?string $locale = null): string
    {
        $slug = $this->getTranslation('slug', $locale ?? app()->getLocale()) ?: $this->id;

        return $this->category
            ? route('news.show', ['category' => $this->category->slug, 'slug' => $slug])
            : route('news.uncategorized', ['slug' => $slug]);
    }
}
