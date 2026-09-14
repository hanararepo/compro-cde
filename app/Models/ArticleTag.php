<?php

namespace App\Models;

use Database\Factories\ArticleTagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class ArticleTag extends Model
{
    /** @use HasFactory<ArticleTagFactory> */
    use HasFactory, HasTranslations;

    /** @var list<string> */
    public array $translatable = ['name'];

    protected $fillable = ['name', 'slug'];

    protected function casts(): array
    {
        return ['name' => 'array'];
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_article_tag');
    }
}
