<?php

namespace App\Models;

use Database\Factories\GalleryCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class GalleryCategory extends Model
{
    /** @use HasFactory<GalleryCategoryFactory> */
    use HasFactory, HasTranslations;

    /** @var list<string> */
    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return ['name' => 'array'];
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }
}
