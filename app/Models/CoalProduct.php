<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CoalProduct extends Model
{
    protected $fillable = ['name', 'specifications'];

    protected function casts(): array
    {
        return ['specifications' => 'array'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $product) {
            $base = Str::limit(Str::slug($product->name), 220, '') ?: 'coal-product';
            $slug = $base;
            $suffix = 2;
            while (static::where('slug', $slug)->exists()) {
                $slug = $base.'-'.$suffix++;
            }
            $product->slug = $slug;
        });

        static::saved(fn () => Cache::forget('sitemap_xml'));
        static::deleted(fn () => Cache::forget('sitemap_xml'));
    }
}
