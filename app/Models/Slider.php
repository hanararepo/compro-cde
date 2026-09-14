<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Slider extends Model
{
    use HasTranslations, SoftDeletes;

    /** @var list<string> */
    public array $translatable = ['title', 'description'];

    protected $fillable = [
        'title',
        'description',
        'image_desktop',
        'image_mobile',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'title'       => 'array',
            'description' => 'array',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer',
        ];
    }

    // ─────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    public function desktopImageUrl(): string
    {
        return asset('storage/' . $this->image_desktop);
    }

    public function mobileImageUrl(): ?string
    {
        return $this->image_mobile ? asset('storage/' . $this->image_mobile) : null;
    }
}
