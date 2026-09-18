<?php

namespace App\Services\Gallery;

use App\Enums\GalleryStatus;
use App\Models\Gallery;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\Laravel\Facades\Image;
use RuntimeException;

class GalleryService
{
    /**
     * Store a new gallery item, generating a resized thumbnail automatically.
     *
     * @param  array<string, mixed>  $data
     */
    public function createGallery(array $data, User $uploader): Gallery
    {
        $data = $this->prepareTranslatableData($data);
        $data['uploaded_by'] = $uploader->id;

        /** @var UploadedFile $file */
        $file = $data['image'];
        unset($data['image']);

        $data['image_path'] = $file->store('galleries', 'public');
        $data['thumbnail_path'] = $this->generateThumbnail($file);

        // Workflow status assignment
        if (! $uploader->can('galleries.approve')) {
            $action = $data['_action'] ?? null;
            if ($action === 'request_approval') {
                $data['status'] = GalleryStatus::Pending;
            } else {
                $data['status'] = GalleryStatus::Draft;
            }
        } else {
            $data['status'] = isset($data['status']) ? GalleryStatus::from($data['status']) : GalleryStatus::Published;
            if ($data['status'] === GalleryStatus::Published) {
                $data['approved_by'] = $uploader->id;
                $data['approved_at'] = now();
            }
        }

        unset($data['_action']);

        return Gallery::create($data);
    }

    /**
     * Update gallery metadata; replace the image only if a new file is provided.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateGallery(Gallery $gallery, array $data, User $editor): Gallery
    {
        $data = $this->prepareTranslatableData($data);

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            // Clean up old files
            Storage::disk('public')->delete(array_filter([$gallery->image_path, $gallery->thumbnail_path]));

            /** @var UploadedFile $file */
            $file = $data['image'];
            $data['image_path'] = $file->store('galleries', 'public');
            $data['thumbnail_path'] = $this->generateThumbnail($file);
            unset($data['image']);
        } else {
            unset($data['image']);
        }

        // Workflow action handling for non-approvers
        if (! $editor->can('galleries.approve')) {
            $action = $data['_action'] ?? null;

            if ($action === 'request_approval') {
                $data['status'] = GalleryStatus::Pending;
                $data['rejection_reason'] = null;
            } elseif ($action === 'set_draft') {
                $data['status'] = GalleryStatus::Draft;
                $data['rejection_reason'] = null;
            } else {
                $data['status'] = $gallery->status;
            }
        } else {
            if (isset($data['status'])) {
                $data['status'] = GalleryStatus::from($data['status']);
                if ($data['status'] === GalleryStatus::Published && ! $gallery->approved_at) {
                    $data['approved_by'] = $editor->id;
                    $data['approved_at'] = now();
                }
            }
        }

        unset($data['_action']);

        $gallery->update($data);

        return $gallery->fresh();
    }

    /**
     * Approve a pending gallery image and publish it.
     */
    public function approveGallery(Gallery $gallery, User $approver): Gallery
    {
        $gallery->update([
            'status' => GalleryStatus::Published,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => null,
            'is_active' => true,
        ]);

        return $gallery->fresh();
    }

    /**
     * Reject a pending gallery image with an optional feedback note.
     */
    public function rejectGallery(Gallery $gallery, User $approver, string $reason = ''): Gallery
    {
        $gallery->update([
            'status' => GalleryStatus::Rejected,
            'rejection_reason' => $reason,
        ]);

        return $gallery->fresh();
    }

    /**
     * Permanently delete the gallery record and its associated files.
     */
    public function deleteGallery(Gallery $gallery): void
    {
        Storage::disk('public')->delete(array_filter([$gallery->image_path, $gallery->thumbnail_path]));
        $gallery->forceDelete();
    }

    /**
     * Generate a thumbnail (max 1600px wide, aspect ratio preserved) and store it
     * under galleries/thumbnails/. Returns the relative storage path.
     *
     * Keep enough detail for large gallery tiles and high-density screens,
     * without cropping or enlarging smaller originals.
     */
    private function generateThumbnail(UploadedFile $file): string
    {
        $thumbnailPath = 'galleries/thumbnails/'.$file->hashName();

        try {
            $thumbnail = $this->encodeThumbnail($file->getRealPath());
            if (! Storage::disk('public')->put($thumbnailPath, $thumbnail)) {
                throw new RuntimeException('Unable to store gallery thumbnail.');
            }
        } catch (\Throwable) {
            $thumbnailPath = $file->store('galleries/thumbnails', 'public');
        }

        return $thumbnailPath;
    }

    /**
     * Rebuild from the original, using a fresh URL to bypass cached thumbnails.
     * Keep the previous file intact until the new thumbnail has been saved.
     */
    public function regenerateThumbnail(Gallery $gallery): string
    {
        $disk = Storage::disk('public');
        $source = $disk->get($gallery->image_path);
        if ($source === null) {
            throw new RuntimeException('Original gallery image is missing.');
        }

        $thumbnail = $this->encodeThumbnail($source);
        $extension = pathinfo($gallery->image_path, PATHINFO_EXTENSION);
        $thumbnailPath = 'galleries/thumbnails/'.Str::uuid().'.'.$extension;

        if (! $disk->put($thumbnailPath, $thumbnail)) {
            throw new RuntimeException('Unable to store gallery thumbnail.');
        }

        $gallery->updateQuietly(['thumbnail_path' => $thumbnailPath]);

        return $thumbnailPath;
    }

    private function encodeThumbnail(string $source): string
    {
        return Image::decode($source)
            ->scaleDown(width: 1600)
            ->encode(new AutoEncoder(quality: 90))
            ->toString();
    }

    /**
     * Build translatable arrays for title/description from locale-keyed inputs.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepareTranslatableData(array $data): array
    {
        foreach (['title', 'description'] as $field) {
            $translations = [];
            foreach (['en', 'id'] as $locale) {
                $key = "{$field}_{$locale}";
                if (isset($data[$key])) {
                    $translations[$locale] = $data[$key];
                    unset($data[$key]);
                }
            }
            if (! empty($translations)) {
                $data[$field] = $translations;
            }
        }

        return $data;
    }
}
