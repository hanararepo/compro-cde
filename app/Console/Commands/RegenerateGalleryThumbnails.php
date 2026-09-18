<?php

namespace App\Console\Commands;

use App\Models\Gallery;
use App\Services\Gallery\GalleryService;
use Illuminate\Console\Command;
use Throwable;

class RegenerateGalleryThumbnails extends Command
{
    protected $signature = 'gallery:regenerate-thumbnails {--id=* : Only regenerate these gallery IDs}';

    protected $description = 'Regenerate larger gallery thumbnails from the original images';

    public function handle(GalleryService $service): int
    {
        $photos = Gallery::query()
            ->when($this->option('id'), fn ($query, $ids) => $query->whereIn('id', $ids))
            ->lazyById();
        $completed = 0;
        $failed = 0;

        foreach ($photos as $photo) {
            try {
                $service->regenerateThumbnail($photo);
                $completed++;
                $this->line("Regenerated gallery #{$photo->id}.");
            } catch (Throwable $exception) {
                $failed++;
                $this->error("Gallery #{$photo->id}: {$exception->getMessage()}");
            }
        }

        $this->info("Regenerated: {$completed}. Failed: {$failed}.");

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
