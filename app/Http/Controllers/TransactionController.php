<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Services\ReorderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Transaction::class);
        $transactions = Transaction::with(['product', 'user'])
            ->latest()
            ->paginate(20);

        return view('transactions.index', compact('transactions'));
    }

    public function create(): View
    {
        $this->authorize('create', Transaction::class);

        return view('transactions.create', [
            'products' => Product::orderBy('name')->get(),
            'transaction' => new Transaction,
        ]);
    }

    public function store(Request $request, ReorderService $reorderService): RedirectResponse
    {
        $this->authorize('create', Transaction::class);
        $data = $this->validateTransaction($request);

        $product = DB::transaction(function () use ($data, $request): Product {
            $product = Product::query()->lockForUpdate()->findOrFail($data['product_id']);
            $nextQuantity = $product->quantity + self::signedQuantity($data['type'], $data['quantity']);

            if ($nextQuantity < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'The requested stock-out quantity exceeds available inventory.',
                ]);
            }

            $product->quantity = $nextQuantity;
            $product->save();

            Transaction::create([
                'product_id' => $product->getKey(),
                'user_id' => $request->user()->getKey(),
                'type' => $data['type'],
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_price'] ?? $product->unit_price,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            return $product;
        });

        $reorderService->checkReorderPoint($product);

        return redirect()->route('transactions.index')->with('success', 'Inventory transaction recorded.');
    }

    public function show(Transaction $transaction): View
    {
        $this->authorize('view', $transaction);
        $transaction->load(['product', 'user']);

        return view('transactions.show', compact('transaction'));
    }

    public function edit(Transaction $transaction): View
    {
        $this->authorize('update', $transaction);

        return view('transactions.edit', [
            'transaction' => $transaction,
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Transaction $transaction, ReorderService $reorderService): RedirectResponse
    {
        $this->authorize('update', $transaction);
        $data = $this->validateTransaction($request);

        $affectedProducts = DB::transaction(function () use ($data, $transaction): array {
            $lockedTransaction = Transaction::query()->lockForUpdate()->findOrFail($transaction->getKey());
            $productIds = collect([$lockedTransaction->product_id, $data['product_id']])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->sort()
                ->values();
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $productIds->count()) {
                abort(404, 'A transaction product is no longer available.');
            }

            $oldProduct = $products->get((int) $lockedTransaction->product_id);
            $newProduct = $products->get((int) $data['product_id']);
            $oldFinalQuantity = $oldProduct->quantity - self::signedQuantity(
                $lockedTransaction->type,
                $lockedTransaction->quantity,
            );
            $newFinalQuantity = $oldProduct->is($newProduct)
                ? $oldFinalQuantity + self::signedQuantity($data['type'], $data['quantity'])
                : $newProduct->quantity + self::signedQuantity($data['type'], $data['quantity']);

            if ($oldFinalQuantity < 0 || $newFinalQuantity < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'This change would make available inventory negative.',
                ]);
            }

            $oldProduct->quantity = $oldFinalQuantity;
            $oldProduct->save();

            if (! $oldProduct->is($newProduct)) {
                $newProduct->quantity = $newFinalQuantity;
                $newProduct->save();
            } else {
                $oldProduct->quantity = $newFinalQuantity;
                $oldProduct->save();
            }

            $lockedTransaction->update([
                'product_id' => $newProduct->getKey(),
                'type' => $data['type'],
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_price'] ?? $newProduct->unit_price,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            return [$oldProduct, $newProduct];
        });

        foreach ($affectedProducts as $product) {
            $reorderService->checkReorderPoint($product);
        }

        return redirect()->route('transactions.show', $transaction)->with('success', 'Transaction updated.');
    }

    public function destroy(Transaction $transaction, ReorderService $reorderService): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $product = DB::transaction(function () use ($transaction): Product {
            $lockedTransaction = Transaction::query()->lockForUpdate()->findOrFail($transaction->getKey());
            $product = Product::query()->lockForUpdate()->findOrFail($lockedTransaction->product_id);
            $nextQuantity = $product->quantity - self::signedQuantity(
                $lockedTransaction->type,
                $lockedTransaction->quantity,
            );

            if ($nextQuantity < 0) {
                throw ValidationException::withMessages([
                    'transaction' => 'This transaction cannot be removed because its stock has already been used.',
                ]);
            }

            $product->quantity = $nextQuantity;
            $product->save();
            $lockedTransaction->delete();

            return $product;
        });

        $reorderService->checkReorderPoint($product);

        return redirect()->route('transactions.index')->with('success', 'Transaction archived and stock adjusted.');
    }

    private function validateTransaction(Request $request): array
    {
        return $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'type' => ['required', 'in:in,out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private static function signedQuantity(string $type, int $quantity): int
    {
        return $type === 'in' ? $quantity : -$quantity;
    }
}
