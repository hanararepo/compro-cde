<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class GalleryVideo extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    /**
     * The attributes that are translatable.
     *
     * @var list<string>
     */
    public array $translatable = ['title'];

    protected $fillable = [
        'title',
        'youtube_url',
        'youtube_id',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (GalleryVideo $video) {
            if ($video->isDirty('youtube_url') || empty($video->youtube_id)) {
                $video->youtube_id = static::extractYouTubeId($video->youtube_url);
            }
        });
    }

    /**
     * Extract the standard 11-character YouTube video ID from various URL patterns.
     */
    public static function extractYouTubeId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        // Handle iframe src if user pasted full embed code
        if (preg_match('/src=["\']([^"\']+)["\']/', $url, $match)) {
            $url = $match[1];
        }

        // Match standard watch?v=, youtu.be/, embed/, shorts/, and v/ formats
        $pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        // If user directly entered the 11-char ID
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', trim($url))) {
            return trim($url);
        }

        return null;
    }

    /**
     * Get the standardized YouTube embed URL.
     */
    public function embedUrl(): string
    {
        $id = $this->youtube_id ?: static::extractYouTubeId($this->youtube_url);

        return $id ? "https://www.youtube.com/embed/{$id}" : $this->youtube_url;
    }

    /**
     * Get the YouTube thumbnail, optionally at its highest available resolution.
     */
    public function thumbnailUrl(bool $highResolution = false): string
    {
        $id = $this->youtube_id ?: static::extractYouTubeId($this->youtube_url);
        $filename = $highResolution ? 'maxresdefault.jpg' : 'hqdefault.jpg';

        return $id ? "https://img.youtube.com/vi/{$id}/{$filename}" : '';
    }

    /**
     * Scope for active videos.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
