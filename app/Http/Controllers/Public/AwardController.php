<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Award;
use Illuminate\View\View;

class AwardController extends Controller
{
    /**
     * Display the public Awards & Certificates page.
     * Awards and certificates are separated by the 'type' column.
     */
    public function index(): View
    {
        $awards = Award::active()
            ->byType('award')
            ->orderBy('sort_order')
            ->latest()
            ->get();

        $certificates = Award::active()
            ->byType('certificate')
            ->orderBy('sort_order')
            ->latest()
            ->get();

        return view('public.about.awards-certificates', compact('awards', 'certificates'));
    }
}
