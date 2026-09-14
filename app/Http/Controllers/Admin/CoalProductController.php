<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveCoalProductRequest;
use App\Models\CoalProduct;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CoalProductController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CoalProduct::class);
        $request->validate(['search' => ['nullable', 'string', 'max:255']]);
        $products = CoalProduct::query()
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->trim().'%'))
            ->latest('created_at')->latest('id')->paginate(12)->withQueryString();

        return view('admin.coal-products.index', compact('products'));
    }

    public function create(): View
    {
        $this->authorize('create', CoalProduct::class);

        return view('admin.coal-products.form', ['coalProduct' => new CoalProduct]);
    }

    public function store(SaveCoalProductRequest $request): RedirectResponse
    {
        $this->authorize('create', CoalProduct::class);
        DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $product = CoalProduct::create([
                'name'           => $validated['name'],
                'specifications' => $this->buildSpecifications($validated),
            ]);
            $this->activityLog->log($request->user(), 'created', "Created coal product '{$product->name}'.", $product);
        });

        return $this->redirectToList($request, 'Coal product created successfully.');
    }

    public function edit(CoalProduct $coalProduct): View
    {
        $this->authorize('update', $coalProduct);

        return view('admin.coal-products.form', compact('coalProduct'));
    }

    public function update(SaveCoalProductRequest $request, CoalProduct $coalProduct): RedirectResponse
    {
        $this->authorize('update', $coalProduct);
        DB::transaction(function () use ($request, $coalProduct) {
            $validated = $request->validated();
            $coalProduct->update([
                'name'           => $validated['name'],
                'specifications' => $this->buildSpecifications($validated),
            ]);
            $this->activityLog->log($request->user(), 'updated', "Updated coal product '{$coalProduct->name}'.", $coalProduct);
        });

        return $this->redirectToList($request, 'Coal product updated successfully.');
    }

    public function destroy(Request $request, CoalProduct $coalProduct): RedirectResponse
    {
        $this->authorize('delete', $coalProduct);
        DB::transaction(function () use ($request, $coalProduct) {
            $this->activityLog->log($request->user(), 'deleted', "Deleted coal product '{$coalProduct->name}'.", $coalProduct);
            $coalProduct->delete();
        });

        return $this->redirectToList($request, 'Coal product deleted successfully.');
    }

    /**
     * Build the specifications JSON from validated columns + rows.
     *
     * Result: { "columns": ["col1", ...], "rows": [["v1", "v2", ...], ...] }
     */
    private function buildSpecifications(array $validated): array
    {
        $columns = array_values(array_map('strval', $validated['columns']));
        $rows    = array_values(array_map(
            fn ($row) => array_values(array_map('strval', $row)),
            $validated['rows']
        ));

        return ['columns' => $columns, 'rows' => $rows];
    }

    private function redirectToList(Request $request, string $message): RedirectResponse
    {
        return redirect()->route($request->user()->can('coal-products.view') ? 'admin.coal-products.index' : 'admin.dashboard')
            ->with('success', $message);
    }
}
