<?php

namespace App\Services\Article;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ArticleService
{
    /**
     * Create a new article.
     * Authors automatically get `pending` status; Admins/Editors get `draft`.
     *
     * @param  array<string, mixed>  $data
     */
    public function createArticle(array $data, User $author): Article
    {
        $data = $this->prepareTranslatableData($data);
        $data['author_id'] = $author->id;
        $data['thumbnail'] = $this->handleThumbnailUpload($data['thumbnail'] ?? null);

        // Non-approvers: support direct submission for approval or saving as draft
        if (! $author->can('articles.approve')) {
            $action = $data['_action'] ?? null;
            if ($action === 'request_approval') {
                $data['status'] = ArticleStatus::Pending;
            } else {
                $data['status'] = ArticleStatus::Draft;
            }
        } else {
            // Approvers always send status (validated as required); cast to enum
            $data['status'] = ArticleStatus::from($data['status']);
        }

        unset($data['_action']);

        return Article::create($data);
    }

    /**
     * Update an existing article.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateArticle(Article $article, array $data, User $editor): Article
    {
        // Pass article ID so generateUniqueSlug excludes self from uniqueness check
        $data['_exclude_id'] = $article->id;
        $data = $this->prepareTranslatableData($data);

        if (isset($data['thumbnail']) && $data['thumbnail'] instanceof UploadedFile) {
            $data['thumbnail'] = $this->handleThumbnailUpload($data['thumbnail']);
        } else {
            unset($data['thumbnail']);
        }

        // Non-approvers cannot freely set status — actions are driven by explicit _action field
        if (! $editor->can('articles.approve')) {
            $action = $data['_action'] ?? null;

            if ($action === 'request_approval') {
                $data['status'] = ArticleStatus::Pending;
                $data['rejection_reason'] = null;
            } elseif ($action === 'set_draft') {
                $data['status'] = ArticleStatus::Draft;
                $data['rejection_reason'] = null;
            } else {
                // Default save: preserve current status
                $data['status'] = $article->status;
            }
        }

        unset($data['_action']);

        $article->update($data);

        if (isset($data['tags'])) {
            $article->tags()->sync($data['tags']);
        }

        return $article->fresh();
    }

    /**
     * Approve an article and set it to published.
     */
    public function approveArticle(Article $article, User $approver): Article
    {
        $article->update([
            'status' => ArticleStatus::Published,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'published_at' => $article->published_at ?? now(),
            'rejection_reason' => null,
        ]);

        return $article->fresh();
    }

    /**
     * Reject an article with an optional reason.
     */
    public function rejectArticle(Article $article, User $reviewer, string $reason = ''): Article
    {
        $article->update([
            'status' => ArticleStatus::Rejected,
            'approved_by' => $reviewer->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $article->fresh();
    }

    /**
     * Move a rejected/draft article back to pending for re-review.
     */
    public function resubmitArticle(Article $article): Article
    {
        $article->update([
            'status'           => ArticleStatus::Pending,
            'rejection_reason' => null,
            'approved_by'      => null,
            'approved_at'      => null,
        ]);

        return $article->fresh();
    }

    /**
     * Unpublish a published article — removes it from the public frontend
     * while keeping the article and its publish history intact.
     */
    public function unpublishArticle(Article $article): Article
    {
        $article->update([
            'status' => ArticleStatus::Unpublished,
        ]);

        return $article->fresh();
    }

    /**
     * Build translatable arrays from locale-keyed form inputs.
     * Guarantees slug generation even when slug fields are omitted.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepareTranslatableData(array $data): array
    {
        $locales = ['en', 'id'];

        // Process title
        $titleTranslations = [];
        foreach ($locales as $locale) {
            if (! empty($data["title_{$locale}"])) {
                $titleTranslations[$locale] = $data["title_{$locale}"];
            }
            unset($data["title_{$locale}"]);
        }
        $data['title'] = $titleTranslations;

        // Process content
        $contentTranslations = [];
        foreach ($locales as $locale) {
            if (! empty($data["content_{$locale}"])) {
                $contentTranslations[$locale] = $data["content_{$locale}"];
            }
            unset($data["content_{$locale}"]);
        }
        $data['content'] = $contentTranslations;

        // Process summary
        $summaryTranslations = [];
        foreach ($locales as $locale) {
            if (! empty($data["summary_{$locale}"])) {
                $summaryTranslations[$locale] = $data["summary_{$locale}"];
            }
            unset($data["summary_{$locale}"]);
        }
        if (! empty($summaryTranslations)) {
            $data['summary'] = $summaryTranslations;
        }

        // Process slug (fallback to slugified title, always unique)
        $excludeId = $data['_exclude_id'] ?? null;
        unset($data['_exclude_id']);

        $slugTranslations = [];
        foreach ($locales as $locale) {
            $slugVal = $data["slug_{$locale}"] ?? '';
            if (empty($slugVal) && ! empty($titleTranslations[$locale])) {
                $slugVal = Str::slug($titleTranslations[$locale]);
            }
            if (! empty($slugVal)) {
                $slugTranslations[$locale] = $this->generateUniqueSlug($slugVal, $locale, $excludeId);
            }
            unset($data["slug_{$locale}"]);
        }
        $data['slug'] = $slugTranslations;

        return $data;
    }

    /**
     * Generate a unique slug for the given locale by appending a numeric suffix
     * if the base slug is already taken by another article.
     *
     * @param  int|null  $excludeId  Article ID to exclude (used on update)
     */
    private function generateUniqueSlug(string $base, string $locale, ?int $excludeId = null): string
    {
        $slug      = $base;
        $suffix    = 1;

        while (true) {
            $exists = Article::whereJsonContains('slug->' . $locale, $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists();

            if (! $exists) {
                return $slug;
            }

            $slug = $base . '-' . $suffix;
            $suffix++;
        }
    }

    /**
     * Upload and store the article thumbnail.
     * Returns the relative storage path or null.
     */
    private function handleThumbnailUpload(?UploadedFile $file): ?string
    {
        if (! $file) {
            return null;
        }

        return $file->store('articles/thumbnails', 'public');
    }
}
