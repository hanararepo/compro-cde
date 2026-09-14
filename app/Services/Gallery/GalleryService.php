<?php

namespace App\Services\Gallery;

use App\Enums\GalleryStatus;
use App\Models\Gallery;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

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
     * Generate a 400×300 thumbnail and store it under galleries/thumbnails/.
     * Returns the relative storage path.
     */
    private function generateThumbnail(UploadedFile $file): string
    {
        $thumbnailPath = 'galleries/thumbnails/'.$file->hashName();

        try {
            $image = Image::decode($file->getRealPath())
                ->cover(400, 300);

            Storage::disk('public')->put($thumbnailPath, $image->encode()->toString());
        } catch (\Throwable) {
            $thumbnailPath = $file->store('galleries/thumbnails', 'public');
        }

        return $thumbnailPath;
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
