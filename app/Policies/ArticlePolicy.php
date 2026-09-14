<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    /**
     * Administrators bypass all policy checks (handled in AppServiceProvider).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('articles.view') || $user->can('articles.view-others');
    }

    public function view(User $user, Article $article): bool
    {
        // Owner can always view their own article
        if ($article->author_id === $user->id) {
            return true;
        }

        return $user->can('articles.view-others');
    }

    public function create(User $user): bool
    {
        return $user->can('articles.create');
    }

    public function update(User $user, Article $article): bool
    {
        // Owner can edit their own draft/pending/rejected articles
        if ($article->author_id === $user->id) {
            return $user->can('articles.edit')
                && in_array($article->status->value, ['draft', 'pending', 'rejected']);
        }

        // Acting on others' articles requires both edit + view-others
        return $user->can('articles.edit') && $user->can('articles.view-others');
    }

    public function delete(User $user, Article $article): bool
    {
        // Owner can delete their own articles
        if ($article->author_id === $user->id) {
            return $user->can('articles.delete');
        }

        // Acting on others' articles requires both delete + view-others
        return $user->can('articles.delete') && $user->can('articles.view-others');
    }

    public function approve(User $user, ?Article $article = null): bool
    {
        return $user->can('articles.approve');
    }
}
