<?php

namespace App\Models;

use App\Enums\GalleryStatus;
use Database\Factories\GalleryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Gallery extends Model
{
    /** @use HasFactory<GalleryFactory> */
    use HasFactory, HasTranslations, SoftDeletes;

    /** @var list<string> */
    public array $translatable = ['title', 'description'];

    protected $fillable = [
        'title',
        'description',
        'image_path',
        'thumbnail_path',
        'alt_text',
        'gallery_category_id',
        'uploaded_by',
        'sort_order',
        'is_active',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'description' => 'array',
            'is_active' => 'boolean',
            'status' => GalleryStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    // ─────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(GalleryCategory::class, 'gallery_category_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    public function imageUrl(): string
    {
        return asset('storage/'.$this->image_path);
    }

    public function thumbnailUrl(): string
    {
        $path = $this->thumbnail_path ?? $this->image_path;

        return asset('storage/'.$path);
    }

    // ─────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('status', GalleryStatus::Published)->where('is_active', true);
    }
}
