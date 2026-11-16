<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\ReorderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);
        
        $products = Product::with(['brand', 'category', 'supplier'])
            ->filter($request->only(['search', 'brand_id', 'category_id', 'stock']))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'brands' => Brand::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);
        return view('products.create', $this->formOptions());
    }

    public function store(Request $request, ReorderService $reorderService): RedirectResponse
    {
        $this->authorize('create', Product::class);
        
        $product = Product::create($this->validateProduct($request));
        $reorderService->checkReorderPoint($product);

        return redirect()->route('products.index')->with('success', 'Product added.');
    }

    public function show(Product $product): View
    {
        $this->authorize('view', $product);
        $product->load(['brand', 'category', 'supplier']);
        
        $transactions = $product->transactions()->with('user')->latest()->paginate(10);

        return view('products.show', compact('product', 'transactions'));
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);
        return view('products.edit', $this->formOptions() + ['product' => $product]);
    }

    public function update(Request $request, Product $product, ReorderService $reorderService): RedirectResponse
    {
        $this->authorize('update', $product);
        
        $product->update($this->validateProduct($request, $product));
        $reorderService->checkReorderPoint($product);

        return redirect()->route('products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product archived.');
    }

    private function formOptions(): array
    {
        return [
            'brands' => Brand::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ];
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product)],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'quantity' => [$product ? 'sometimes' : 'required', 'integer', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'description' => ['nullable', 'string'],
        ]);
    }
}