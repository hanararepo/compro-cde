<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\GalleryCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AboutController extends Controller
{
    /**
     * Display the About Us photo gallery page.
     * Photos are pulled from the Gallery CRUD in the admin dashboard.
     */
    public function photoGallery(Request $request): View
    {
        $galleries = Gallery::with('category')
            ->published()
            ->when($request->category, function ($query, $categorySlug) {
                $query->whereHas('category', function ($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            })
            ->orderBy('sort_order')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = GalleryCategory::withCount(['galleries' => function ($query) {
            $query->published();
        }])->get();

        return view('public.about.photo-gallery', compact('galleries', 'categories'));
    }
}
