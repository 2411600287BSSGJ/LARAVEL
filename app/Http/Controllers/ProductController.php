<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::with(['brand', 'category', 'supplier'])
            ->filter($request->only(['search', 'category', 'category_id', 'brand_id', 'stock']));

        $products = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Hardware store default categories
        $defaultCategories = ['Lumber', 'Hardware', 'Tools', 'Electrical', 'Plumbing', 'Paint'];
        $categories = Category::orderBy('name')->pluck('name')->toArray();
        $allCategories = array_values(array_unique(array_merge($defaultCategories, $categories)));

        return view('products.index', [
            'products' => $products,
            'brands' => Brand::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'categoryList' => $allCategories,
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('products.create', $this->formOptions());
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $validated = $this->validateProduct($request);

        // Sync category string with category_id if provided
        if (!empty($validated['category_id']) && empty($validated['category'])) {
            $cat = Category::find($validated['category_id']);
            $validated['category'] = $cat?->name;
        } elseif (!empty($validated['category']) && empty($validated['category_id'])) {
            $cat = Category::firstOrCreate(['name' => $validated['category']]);
            $validated['category_id'] = $cat->id;
        }

        // Sync supplier string with supplier_id if provided
        if (!empty($validated['supplier_id']) && empty($validated['supplier'])) {
            $sup = Supplier::find($validated['supplier_id']);
            $validated['supplier'] = $sup?->name;
        } elseif (!empty($validated['supplier']) && empty($validated['supplier_id'])) {
            $sup = Supplier::firstOrCreate(['name' => $validated['supplier']]);
            $validated['supplier_id'] = $sup->id;
        }

        $product = Product::create($validated);

        // Record initial stock transaction if quantity > 0
        if ($product->quantity > 0) {
            InventoryTransaction::withoutEvents(function () use ($product) {
                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'stock_in',
                    'quantity' => $product->quantity,
                    'unit_price' => $product->unit_price,
                    'reference_number' => 'INIT-' . $product->sku,
                    'notes' => 'Initial stock on creation',
                ]);
            });
        }

        return redirect()->route('products.index')->with('success', "Product '{$product->name}' created successfully.");
    }

    /**
     * Display the specified product with transaction history and running balance.
     */
    public function show(Request $request, Product $product): View
    {
        $this->authorize('view', $product);

        $product->load(['brand', 'category', 'supplier']);

        // Transaction history with optional date range filtering
        $txQuery = $product->transactions()->with('user')->orderBy('created_at', 'asc')->orderBy('id', 'asc');

        if ($request->filled('start_date')) {
            $txQuery->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $txQuery->whereDate('created_at', '<=', $request->end_date);
        }

        $allTransactions = $txQuery->get();

        // Compute running balance
        $runningBalance = 0;
        foreach ($allTransactions as $tx) {
            $type = strtolower($tx->type);
            $qty = (int) $tx->quantity;

            if (in_array($type, ['stock_in', 'in', 'purchase'])) {
                $runningBalance += abs($qty);
            } elseif (in_array($type, ['stock_out', 'out', 'sale'])) {
                $runningBalance -= abs($qty);
            } elseif ($type === 'adjustment') {
                $runningBalance += $qty;
            }
            $tx->running_balance = $runningBalance;
        }

        // Display newest first in view
        $transactions = $allTransactions->reverse()->values();

        return view('products.show', [
            'product' => $product,
            'transactions' => $transactions,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
        ]);
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('products.edit', $this->formOptions() + ['product' => $product]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $validated = $this->validateProduct($request, $product);

        if (!empty($validated['category_id']) && empty($validated['category'])) {
            $cat = Category::find($validated['category_id']);
            $validated['category'] = $cat?->name;
        } elseif (!empty($validated['category']) && empty($validated['category_id'])) {
            $cat = Category::firstOrCreate(['name' => $validated['category']]);
            $validated['category_id'] = $cat->id;
        }

        if (!empty($validated['supplier_id']) && empty($validated['supplier'])) {
            $sup = Supplier::find($validated['supplier_id']);
            $validated['supplier'] = $sup?->name;
        } elseif (!empty($validated['supplier']) && empty($validated['supplier_id'])) {
            $sup = Supplier::firstOrCreate(['name' => $validated['supplier']]);
            $validated['supplier_id'] = $sup->id;
        }

        $product->update($validated);

        return redirect()->route('products.index')->with('success', "Product '{$product->name}' updated successfully.");
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $name = $product->name;
        $product->delete();

        return redirect()->route('products.index')->with('success', "Product '{$name}' deleted successfully.");
    }

    /**
     * Provide shared options for forms.
     */
    private function formOptions(): array
    {
        $defaultCategories = ['Lumber', 'Hardware', 'Tools', 'Electrical', 'Plumbing', 'Paint'];
        $dbCategories = Category::orderBy('name')->pluck('name')->toArray();
        $allCategories = array_values(array_unique(array_merge($defaultCategories, $dbCategories)));

        return [
            'brands' => Brand::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'categoryList' => $allCategories,
        ];
    }

    /**
     * Validate product attributes with custom messages.
     */
    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product)],
            'category' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'quantity' => ['required', 'integer', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'description' => ['nullable', 'string'],
        ];

        $messages = [
            'name.required' => 'The product name is required.',
            'sku.required' => 'The SKU (Stock Keeping Unit) code is required.',
            'sku.unique' => 'This SKU is already in use by another product.',
            'quantity.required' => 'Please enter the initial quantity in stock.',
            'quantity.min' => 'Quantity cannot be negative.',
            'reorder_level.required' => 'Please specify a reorder level threshold.',
            'reorder_level.min' => 'Reorder level cannot be negative.',
            'unit_price.required' => 'A valid unit price is required.',
            'unit_price.min' => 'Unit price cannot be negative.',
        ];

        return $request->validate($rules, $messages);
    }
}
