<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CoalProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CoalProductController extends Controller
{
    public function index(): RedirectResponse|View
    {
        $firstProduct = CoalProduct::latest('created_at')->latest('id')->first();

        if ($firstProduct) {
            return redirect()->route('coal-products.show', ['coalProduct' => $firstProduct->slug]);
        }

        $products = collect();
        return view('public.coal-products.index', compact('products'));
    }

    public function show(CoalProduct $coalProduct): View
    {
        return view('public.coal-products.show', compact('coalProduct'));
    }
}
