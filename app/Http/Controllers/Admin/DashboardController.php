<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\StatisticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly StatisticsService $statisticsService
    ) {}

    /**
     * Display the admin dashboard with summary metrics.
     */
    public function index(Request $request): View
    {
        $stats = $this->statisticsService->getDashboardStats($request->user());

        return view('admin.dashboard.index', compact('stats'));
    }
}
