<?php

namespace App\Services\Dashboard;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ContactMessage;
use App\Models\Gallery;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class StatisticsService
{
    /**
     * Aggregate rich dashboard statistics for admin overview, strictly scoped by user permissions.
     *
     * @return array{
     *     can_view_other_articles: bool,
     *     can_view_galleries: bool,
     *     can_approve_articles: bool,
     *     can_view_contact_messages: bool,
     *     total_articles: int,
     *     published_articles: int,
     *     pending_articles: int,
     *     total_views: int,
     *     total_galleries: int,
     *     total_users: int,
     *     total_messages: int,
     *     unread_messages: int,
     *     recent_messages: Collection,
     *     top_articles: Collection,
     *     recent_articles: Collection,
     *     pending_list: Collection,
     *     category_distribution: array<int, array{name: string, count: int, percentage: int, color: string}>,
     *     monthly_trends: array<int, array{month: string, count: int, height: int}>,
     * }
     */
    public function getDashboardStats(?User $user = null): array
    {
        $user = $user ?? auth()->user();
        $canViewOtherArticles = $user ? ($user->can('articles.view-others') || $user->hasRole('Administrator')) : true;
        $canViewOtherGalleries = $user ? ($user->can('galleries.view-others') || $user->hasRole('Administrator')) : true;
        $canViewGalleries = $user ? ($user->can('galleries.view') || $user->can('galleries.view-others') || $user->hasRole('Administrator')) : true;
        $canApproveArticles = $user ? ($user->can('articles.approve') || $user->hasRole('Administrator')) : false;
        $canViewContactMessages = $user ? ($user->can('contact-messages.view') || $user->hasRole('Administrator')) : false;

        // Article base query scoped by user permission
        $articleQuery = Article::query()
            ->when(! $canViewOtherArticles && $user, function ($q) use ($user) {
                $q->where('author_id', $user->id);
            });

        $totalArticles = (clone $articleQuery)->count();
        $publishedCount = (clone $articleQuery)->where('status', ArticleStatus::Published)->count();
        $pendingCount = (clone $articleQuery)->where('status', ArticleStatus::Pending)->count();
        $totalViews = (int) (clone $articleQuery)->sum('views_count');

        // Top 5 Most Viewed Articles
        $topArticles = (clone $articleQuery)->where('status', ArticleStatus::Published)
            ->with(['author', 'category'])
            ->orderByDesc('views_count')
            ->take(5)
            ->get();

        // 5 Pending Approval Articles
        $pendingList = (clone $articleQuery)->where('status', ArticleStatus::Pending)
            ->with(['author', 'category'])
            ->latest()
            ->take(5)
            ->get();

        // Recent 5 Articles
        $recentArticles = (clone $articleQuery)
            ->with(['author', 'category'])
            ->latest()
            ->take(5)
            ->get();

        // Category breakdown with percentage
        $categories = ArticleCategory::withCount(['articles' => function ($q) use ($canViewOtherArticles, $user) {
            $q->where('status', ArticleStatus::Published)
                ->when(! $canViewOtherArticles && $user, fn ($sq) => $sq->where('author_id', $user->id));
        }])
            ->orderByDesc('articles_count')
            ->take(6)
            ->get();

        $categoryColors = ['#6366f1', '#ec4899', '#f59e0b', '#10b981', '#06b6d4', '#8b5cf6'];
        $categoryDistribution = [];

        foreach ($categories as $index => $cat) {
            $count = $cat->articles_count;
            $percentage = $publishedCount > 0 ? (int) round(($count / $publishedCount) * 100) : 0;
            $categoryDistribution[] = [
                'name' => $cat->getTranslation('name', 'en') ?: $cat->slug,
                'count' => $count,
                'percentage' => $percentage,
                'color' => $cat->color ?: ($categoryColors[$index % count($categoryColors)]),
            ];
        }

        // Monthly publishing trend (Last 6 Months)
        $monthlyTrends = [];
        $now = Carbon::now();
        $trendCounts = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = (clone $now)->subMonths($i);
            $year = $monthDate->year;
            $month = $monthDate->month;

            $count = (clone $articleQuery)->where('status', ArticleStatus::Published)
                ->whereYear('published_at', $year)
                ->whereMonth('published_at', $month)
                ->count();

            $trendCounts[] = [
                'month' => $monthDate->format('M'),
                'count' => $count,
            ];
        }

        $maxMonthly = max(array_column($trendCounts, 'count') ?: [1]);
        $maxMonthly = $maxMonthly > 0 ? $maxMonthly : 1;

        foreach ($trendCounts as $trend) {
            $monthlyTrends[] = [
                'month' => $trend['month'],
                'count' => $trend['count'],
                'height' => (int) max(8, round(($trend['count'] / $maxMonthly) * 100)),
            ];
        }

        // Galleries count scoped
        $totalGalleries = $canViewGalleries
            ? Gallery::query()
                ->when(! $canViewOtherGalleries && $user, fn ($q) => $q->where('uploaded_by', $user->id))
                ->count()
            : 0;

        // Contact messages count & list scoped by permission
        $totalMessages = $canViewContactMessages ? ContactMessage::count() : 0;
        $unreadMessages = $canViewContactMessages ? ContactMessage::unread()->count() : 0;
        $recentMessages = $canViewContactMessages ? ContactMessage::latest()->take(5)->get() : collect();

        return [
            'can_view_other_articles' => $canViewOtherArticles,
            'can_view_galleries' => $canViewGalleries,
            'can_approve_articles' => $canApproveArticles,
            'can_view_contact_messages' => $canViewContactMessages,
            'total_articles' => $totalArticles,
            'published_articles' => $publishedCount,
            'pending_articles' => $pendingCount,
            'total_views' => $totalViews,
            'total_galleries' => $totalGalleries,
            'total_users' => User::count(),
            'total_messages' => $totalMessages,
            'unread_messages' => $unreadMessages,
            'recent_messages' => $recentMessages,
            'top_articles' => $topArticles,
            'recent_articles' => $recentArticles,
            'pending_list' => $pendingList,
            'category_distribution' => $categoryDistribution,
            'monthly_trends' => $monthlyTrends,
        ];
    }
}
