<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Brand::class);

        return view('brands.index', ['brands' => Brand::withCount('products')->orderBy('name')->paginate(15)]);
    }

    public function show(Brand $brand): View
    {
        $this->authorize('view', $brand);

        return view('catalog.show', [
            'kind' => 'Brand',
            'record' => $brand,
            'products' => $brand->products()->orderBy('name')->paginate(15),
            'routePrefix' => 'brands',
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Brand::class);

        return view('catalog.create', ['kind' => 'Brand', 'routePrefix' => 'brands', 'record' => new Brand]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Brand::class);
        Brand::create($this->validateRecord($request));

        return redirect()->route('brands.index')->with('success', 'Manufacturer added.');
    }

    public function edit(Brand $brand): View
    {
        $this->authorize('update', $brand);

        return view('catalog.edit', ['kind' => 'Brand', 'routePrefix' => 'brands', 'record' => $brand]);
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $this->authorize('update', $brand);
        $brand->update($this->validateRecord($request, $brand));

        return redirect()->route('brands.index')->with('success', 'Manufacturer updated.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $this->authorize('delete', $brand);
        $brand->delete();

        return redirect()->route('brands.index')->with('success', 'Manufacturer archived.');
    }

    private function validateRecord(Request $request, ?Brand $brand = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('brands', 'name')->ignore($brand)],
            'description' => ['nullable', 'string'],
        ]);
    }
}
