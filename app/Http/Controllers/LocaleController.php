<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch application language and redirect back.
     */
    public function switch(Request $request, string $lang): RedirectResponse
    {
        if (in_array($lang, ['en', 'id'], true)) {
            session(['locale' => $lang]);

            // If user came from an article page, attempt to redirect to the translated slug
            $previousUrl = url()->previous();
            $path = parse_url($previousUrl, PHP_URL_PATH) ?? '';

            if (preg_match('#^/([^/]+)/([^/]+)$#', $path, $matches)) {
                $prefix = rawurldecode($matches[1]);
                $currentSlug = rawurldecode($matches[2]);
                $article = Article::with('category')->published()
                    ->where(fn ($query) => $query->where('slug->en', $currentSlug)->orWhere('slug->id', $currentSlug))
                    ->when(! in_array($prefix, ['articles', 'news'], true), fn ($query) => $query->whereHas('category', fn ($category) => $category->where('slug', $prefix)))
                    ->first();

                if ($article) {
                    $newSlug = $article->getTranslation('slug', $lang, false);
                    if ($newSlug) {
                        return redirect()->to($article->publicUrl($lang));
                    }
                }
            }
        }

        return redirect()->back();
    }
}
