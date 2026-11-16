<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryTransactionController extends Controller
{
    /**
     * Display a listing of inventory transactions with filtering and pagination.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', InventoryTransaction::class);

        $query = InventoryTransaction::with(['product', 'user']);

        // Filtering by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filtering by type
        if ($request->filled('type')) {
            $type = $request->type;
            if ($type === 'stock_in') {
                $query->whereIn('type', ['stock_in', 'in', 'inbound', 'purchase']);
            } elseif ($type === 'stock_out') {
                $query->whereIn('type', ['stock_out', 'out', 'outbound', 'sale']);
            } elseif ($type === 'adjustment') {
                $query->where('type', 'adjustment');
            } else {
                $query->where('type', $type);
            }
        }

        // Filtering by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $transactions = $query->latest()->paginate(20)->withQueryString();
        $products = Product::orderBy('name')->get();

        $types = [
            'stock_in' => 'Stock In',
            'stock_out' => 'Stock Out',
            'adjustment' => 'Adjustment',
        ];

        return view('transactions.index', compact('transactions', 'products', 'types'));
    }

    /**
     * Show the form for recording a new transaction.
     */
    public function create(): View
    {
        $this->authorize('create', InventoryTransaction::class);

        $products = Product::orderBy('name')->get();
        $types = [
            'stock_in' => 'Stock In (Replenishment / Purchase)',
            'stock_out' => 'Stock Out (Sale / Usage / Dispatch)',
            'adjustment' => 'Stock Adjustment (Physical Count Audit)',
        ];

        return view('transactions.create', compact('products', 'types'));
    }

    /**
     * Store a newly created transaction in storage.
     * Observer automatically updates product stock and handles notifications.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', InventoryTransaction::class);

        $validator = Validator::make($request->all(), [
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'in:stock_in,stock_out,adjustment,inbound,outbound'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'product_id.required' => 'Please select a product for this transaction.',
            'product_id.exists' => 'The selected product does not exist in inventory.',
            'type.required' => 'Please specify the transaction type.',
            'quantity.required' => 'Transaction quantity is required.',
            'quantity.min' => 'Quantity must be at least 1.',
        ]);

        // After-validation hook: verify stock availability for stock-out transactions
        $validator->after(function ($v) use ($request) {
            $type = strtolower($request->type);
            if (in_array($type, ['stock_out', 'outbound', 'out', 'sale'])) {
                $product = Product::find($request->product_id);
                if ($product && $product->quantity < (int) $request->quantity) {
                    $v->errors()->add(
                        'quantity',
                        "Insufficient stock. Available: {$product->quantity} unit(s). Requested: {$request->quantity} unit(s)."
                    );
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $type = strtolower($validated['type']);
        $reference = $validated['reference_number'] ?? $validated['reference'] ?? ('TXN-' . strtoupper(uniqid()));

        $product = Product::findOrFail($validated['product_id']);

        // Observer will execute stock modification in created event
        InventoryTransaction::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => $type,
            'quantity' => (int) $validated['quantity'],
            'unit_price' => $validated['unit_price'] ?? $product->unit_price,
            'reference_number' => $reference,
            'reference' => $reference,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('transactions.index')->with('success', 'Inventory transaction recorded and stock level updated successfully.');
    }

    /**
     * Display the specified transaction details.
     */
    public function show(InventoryTransaction $transaction): View
    {
        $this->authorize('view', $transaction);

        $transaction->load(['product.category', 'product.brand', 'user']);

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Remove the specified transaction (admin only) and reverse stock effect.
     */
    public function destroy(InventoryTransaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $reference = $transaction->reference_number ?? $transaction->id;
        $transaction->delete(); // Observer's deleted hook reverses stock

        return redirect()->route('transactions.index')->with('success', "Transaction '{$reference}' deleted and stock balance reversed.");
    }

    /**
     * Show form for stock adjustment.
     */
    public function createAdjustment(): View
    {
        $this->authorize('create', InventoryTransaction::class);

        $products = Product::orderBy('name')->get();
        $reasons = [
            'Physical Count Discrepancy' => 'Physical Count Discrepancy (Periodic Audit)',
            'Damaged Goods' => 'Damaged / Broken Goods',
            'Theft/Loss' => 'Theft / Shrinkage / Loss',
            'Internal Store Use' => 'Internal Store Use / Display',
            'Supplier Over-shipment' => 'Supplier Over-shipment / Unrecorded Receipt',
            'Other' => 'Other / Correction',
        ];

        return view('transactions.adjustment', compact('products', 'reasons'));
    }

    /**
     * Process stock adjustment based on physical count.
     */
    public function storeAdjustment(Request $request): RedirectResponse
    {
        $this->authorize('create', InventoryTransaction::class);

        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'physical_count' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::findOrFail($request->product_id);
        $systemQuantity = (int) $product->quantity;
        $physicalCount = (int) $request->physical_count;
        $difference = $physicalCount - $systemQuantity;

        if ($difference === 0) {
            return redirect()->back()->with('info', "Physical count matches current system stock ({$systemQuantity} units). No adjustment needed.");
        }

        $notes = "Adjustment: Physical Count ({$physicalCount}) vs System Stock ({$systemQuantity}). Reason: {$request->reason}";
        if ($request->filled('notes')) {
            $notes .= " | Additional Notes: {$request->notes}";
        }

        InventoryTransaction::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => 'adjustment',
            'quantity' => $difference,
            'unit_price' => $product->unit_price,
            'reference_number' => 'ADJ-' . strtoupper(uniqid()),
            'notes' => $notes,
        ]);

        return redirect()->route('transactions.index')->with(
            'success',
            "Stock adjustment processed for '{$product->name}'. Quantity updated from {$systemQuantity} to {$physicalCount}."
        );
    }
}
