<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Supplier::class);

        return view('suppliers.index', ['suppliers' => Supplier::withCount('purchaseOrders')->orderBy('name')->paginate(15)]);
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
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier archived.');
    }

    private function validateSupplier(Request $request, ?Supplier $supplier = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('suppliers', 'email')->ignore($supplier)],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'lead_time_days' => ['required', 'integer', 'min:0'],
        ]);
    }
}
