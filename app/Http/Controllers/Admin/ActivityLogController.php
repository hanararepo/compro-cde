<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Display a paginated, filterable list of activity log entries.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ActivityLog::class);

        $logs = ActivityLog::with('user')
            ->when($request->event, fn ($q, $event) => $q->forEvent($event))
            ->when($request->subject_type, fn ($q, $type) => $q->forSubjectType("App\\Models\\{$type}"))
            ->when($request->user_id, fn ($q, $userId) => $q->forUser((int) $userId))
            ->when($request->date_from, fn ($q, $date) => $q->fromDate($date))
            ->when($request->date_to, fn ($q, $date) => $q->toDate($date))
            ->when($request->search, function ($q, $search) {
                $q->where('description', 'like', "%{$search}%");
            })
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $users = User::orderBy('name')->get(['id', 'name']);

        $events = [
            'created', 'updated', 'deleted',
            'approved', 'rejected',
            'logged_in', 'logged_out',
        ];

        $subjectTypes = [
            'Article', 'Gallery',
            'ArticleCategory', 'GalleryCategory',
            'ArticleTag', 'User', 'Role',
        ];

        return view('admin.activity-log.index', compact('logs', 'users', 'events', 'subjectTypes'));
    }
}
