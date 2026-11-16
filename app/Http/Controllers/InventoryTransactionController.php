<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Services\ReorderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryTransactionController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', InventoryTransaction::class);
        $transactions = InventoryTransaction::with(['product', 'user'])
            ->latest()
            ->paginate(20);

        return view('transactions.index', compact('transactions'));
    }

    public function create(): View
    {
        $this->authorize('create', InventoryTransaction::class);

        return view('transactions.create', ['products' => Product::orderBy('name')->get()]);
    }

    public function store(Request $request, ReorderService $reorderService): RedirectResponse
    {
        $this->authorize('create', InventoryTransaction::class);
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'in:inbound,outbound'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $product = DB::transaction(function () use ($data, $request): Product {
            $product = Product::query()->lockForUpdate()->findOrFail($data['product_id']);

            if ($data['type'] === 'outbound' && $product->quantity < $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'The requested stock-out quantity exceeds available inventory.',
                ]);
            }

            $delta = $data['type'] === 'inbound' ? $data['quantity'] : -$data['quantity'];
            $product->quantity += $delta;
            $product->save();

            InventoryTransaction::create([
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'type' => $data['type'],
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_price'] ?? $product->unit_price,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            return $product;
        });

        if ($data['type'] === 'outbound') {
            $reorderService->checkReorderPoint($product);
        }

        return redirect()->route('transactions.index')->with('success', 'Inventory transaction recorded.');
    }
}
