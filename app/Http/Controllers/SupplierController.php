<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Supplier::class);

        $query = Supplier::withCount('purchaseOrders')
            ->filter($request->only(['search', 'active']));

        // Sorting options
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');
        if (in_array($sort, ['name', 'lead_time_days', 'purchase_orders_count', 'created_at'])) {
            $query->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('name');
        }

        $suppliers = $query->paginate(15)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function show(Supplier $supplier): View
    {
        $this->authorize('view', $supplier);

        $supplier->load(['products' => fn ($q) => $q->orderBy('name')]);
        $purchaseOrders = $supplier->purchaseOrders()->with(['product', 'items.product'])->latest()->paginate(10);
        $performance = $supplier->calculatePerformance();

        return view('suppliers.show', compact('supplier', 'purchaseOrders', 'performance'));
    }

    public function create(): View
    {
        $this->authorize('create', Supplier::class);

        return view('suppliers.create', ['supplier' => new Supplier]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Supplier::class);
        Supplier::create($this->validateSupplier($request));

        return redirect()->route('suppliers.index')->with('success', 'Supplier added.');
    }

    public function edit(Supplier $supplier): View
    {
        $this->authorize('update', $supplier);

        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $this->authorize('update', $supplier);
        $supplier->update($this->validateSupplier($request, $supplier));

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorize('delete', $supplier);

        // Check for pending purchase orders before deletion
        if ($supplier->purchaseOrders()->where('status', 'pending')->exists()) {
            return redirect()->route('suppliers.index')
                ->with('error', "Cannot delete supplier '{$supplier->name}': There are pending purchase orders awaiting fulfillment.");
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', "Supplier '{$supplier->name}' has been archived.");
    }

    private function validateSupplier(Request $request, ?Supplier $supplier = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('suppliers', 'email')->ignore($supplier)],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'payment_terms' => ['nullable', 'string', 'max:100'],
            'lead_time_days' => ['required', 'integer', 'min:0'],
            'minimum_order_amount' => ['nullable', 'numeric', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ]);
    }
}
