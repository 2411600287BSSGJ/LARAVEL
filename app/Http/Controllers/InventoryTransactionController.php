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
     * Show the form for editing an existing transaction.
     */
    public function edit(InventoryTransaction $transaction): View
    {
        $this->authorize('update', $transaction);

        $transaction->load(['product', 'user']);
        $products = Product::orderBy('name')->get();
        $types = [
            'stock_in' => 'Stock In (Replenishment / Purchase)',
            'stock_out' => 'Stock Out (Sale / Usage / Dispatch)',
            'adjustment' => 'Stock Adjustment (Physical Count Audit)',
        ];

        return view('transactions.edit', compact('transaction', 'products', 'types'));
    }

    /**
     * Update the specified transaction in storage and reconcile stock if quantity changed.
     */
    public function update(Request $request, InventoryTransaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::findOrFail($transaction->product_id);
        $oldQty = (int) $transaction->quantity;
        $newQty = (int) $validated['quantity'];
        $type = strtolower($transaction->type);

        // If quantity changed, reconcile product inventory stock
        if ($oldQty !== $newQty) {
            if (in_array($type, ['stock_in', 'in', 'inbound', 'purchase'])) {
                $difference = $newQty - $oldQty;
                if ($product->quantity + $difference < 0) {
                    return redirect()->back()->withErrors([
                        'quantity' => "Cannot reduce stock in by " . abs($difference) . " units. Current stock is {$product->quantity}.",
                    ])->withInput();
                }
                $product->quantity += $difference;
            } elseif (in_array($type, ['stock_out', 'out', 'outbound', 'sale'])) {
                $difference = $newQty - $oldQty;
                if ($product->quantity < $difference) {
                    return redirect()->back()->withErrors([
                        'quantity' => "Insufficient stock to increase stock out. Available: {$product->quantity} unit(s). Additional required: {$difference} unit(s).",
                    ])->withInput();
                }
                $product->quantity -= $difference;
            } elseif ($type === 'adjustment') {
                $difference = $newQty - $oldQty;
                if ($product->quantity + $difference < 0) {
                    return redirect()->back()->withErrors([
                        'quantity' => "Adjustment would result in negative stock balance.",
                    ])->withInput();
                }
                $product->quantity += $difference;
            }
            $product->save();
        }

        $ref = $validated['reference_number'] ?? $validated['reference'] ?? $transaction->reference_number;

        $transaction->update([
            'quantity' => $newQty,
            'unit_price' => $validated['unit_price'] ?? $transaction->unit_price,
            'reference_number' => $ref,
            'reference' => $ref,
            'notes' => $validated['notes'] ?? $transaction->notes,
        ]);

        return redirect()->route('transactions.show', $transaction)
            ->with('success', "Audit record '{$ref}' updated successfully.");
    }

    /**
     * Export audit trail records to CSV with current active filters.
     */
    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('viewAny', InventoryTransaction::class);

        $query = InventoryTransaction::with(['product', 'user']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

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

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $fileName = 'audit_trail_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Audit ID',
                'Date & Time',
                'Movement Type',
                'Product Name',
                'SKU',
                'Quantity Changed',
                'Unit Price (PHP)',
                'Total Value (PHP)',
                'Reference Number',
                'Encoded By',
                'User Role',
                'Notes',
            ]);

            $query->latest()->chunk(200, function ($transactions) use ($handle) {
                foreach ($transactions as $tx) {
                    $isStockIn = in_array(strtolower($tx->type), ['stock_in', 'in', 'inbound', 'purchase']);
                    $isStockOut = in_array(strtolower($tx->type), ['stock_out', 'out', 'outbound', 'sale']);
                    $sign = $isStockIn ? '+' : ($isStockOut ? '-' : ($tx->quantity >= 0 ? '+' : ''));
                    $qtyDisplay = $sign . abs($tx->quantity);
                    $price = $tx->unit_price ?? $tx->product?->unit_price ?? 0;
                    $total = abs($tx->quantity) * $price;

                    fputcsv($handle, [
                        $tx->id,
                        $tx->created_at ? $tx->created_at->format('Y-m-d H:i:s') : 'N/A',
                        $tx->type_display,
                        $tx->product?->name ?? 'N/A',
                        $tx->product?->sku ?? 'N/A',
                        $qtyDisplay,
                        number_format($price, 2, '.', ''),
                        number_format($total, 2, '.', ''),
                        $tx->reference_number ?? '—',
                        $tx->user?->name ?? 'System',
                        ucfirst($tx->user?->role ?? 'system'),
                        $tx->notes ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Dedicated printable view of the audit trail ledger.
     */
    public function printIndex(Request $request): View
    {
        $this->authorize('viewAny', InventoryTransaction::class);

        $query = InventoryTransaction::with(['product', 'user']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

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

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $transactions = $query->latest()->get();

        return view('transactions.print', compact('transactions'));
    }

    /**
     * Dedicated printable transaction voucher / slip.
     */
    public function printShow(InventoryTransaction $transaction): View
    {
        $this->authorize('view', $transaction);

        $transaction->load(['product.category', 'product.brand', 'user']);

        return view('transactions.print-voucher', compact('transaction'));
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
