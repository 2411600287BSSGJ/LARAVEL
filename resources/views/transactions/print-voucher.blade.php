<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Movement Voucher #{{ $transaction->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: #ffffff;
            color: #212529;
            padding: 30px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
            @page {
                size: portrait;
                margin: 1.5cm;
            }
        }
        .voucher-box {
            border: 2px solid #212529;
            border-radius: 8px;
            padding: 25px;
        }
        .header-box {
            border-bottom: 2px solid #212529;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Top Action Bar (hidden on print) -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print bg-light p-3 rounded border">
        <div>
            <h5 class="mb-0 fw-bold"><i class="bi bi-receipt me-2"></i>Stock Movement Voucher #{{ $transaction->id }}</h5>
            <small class="text-muted">Printable official voucher copy for physical filing and verification.</small>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer-fill me-1"></i>Print Voucher
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i>Close
            </button>
        </div>
    </div>

    <!-- Official Voucher Container -->
    <div class="voucher-box">
        <!-- Header -->
        <div class="header-box d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-danger">🏋️ GYM INVENTORY MANAGEMENT SYSTEM</h3>
                <h5 class="text-uppercase text-secondary mb-0">Stock Movement Audit Voucher</h5>
            </div>
            <div class="text-end">
                <div class="fs-5 fw-bold font-monospace">VOUCHER #{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</div>
                <small class="text-muted">Ref: {{ $transaction->reference_number ?: 'N/A' }}</small>
            </div>
        </div>

        @php
            $isStockIn = in_array(strtolower($transaction->type), ['stock_in', 'in', 'inbound', 'purchase']);
            $isStockOut = in_array(strtolower($transaction->type), ['stock_out', 'out', 'outbound', 'sale']);
            $sign = $isStockIn ? '+' : ($isStockOut ? '-' : ($transaction->quantity >= 0 ? '+' : ''));
            $price = $transaction->unit_price ?? $transaction->product?->unit_price ?? 0;
            $totalVal = abs($transaction->quantity) * $price;
        @endphp

        <!-- Key Information Grid -->
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="p-3 bg-light rounded border">
                    <small class="text-muted text-uppercase fw-bold d-block mb-1">Transaction Information</small>
                    <div><strong>Movement Type:</strong> {{ $transaction->type_display }}</div>
                    <div><strong>Date & Time:</strong> {{ $transaction->created_at ? $transaction->created_at->format('F d, Y h:i:s A') : '—' }}</div>
                    <div><strong>Reference Number:</strong> <code>{{ $transaction->reference_number ?: 'N/A' }}</code></div>
                </div>
            </div>
            <div class="col-6">
                <div class="p-3 bg-light rounded border">
                    <small class="text-muted text-uppercase fw-bold d-block mb-1">Authorization Details</small>
                    <div><strong>Encoded By:</strong> {{ $transaction->user?->name ?? 'System' }}</div>
                    <div><strong>User Role:</strong> {{ ucfirst($transaction->user?->role ?? 'Staff') }}</div>
                    <div><strong>Email:</strong> {{ $transaction->user?->email ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Product Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Total Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $transaction->product?->name ?? 'Archived Product' }}</strong>
                            <small class="text-muted d-block">{{ $transaction->product?->category_name ?? '' }}</small>
                        </td>
                        <td class="font-monospace">{{ $transaction->product?->sku ?? '—' }}</td>
                        <td class="text-center fs-5 fw-bold {{ $isStockIn ? 'text-success' : ($isStockOut ? 'text-danger' : '') }}">
                            {{ $sign }}{{ abs($transaction->quantity) }} units
                        </td>
                        <td class="text-end">₱{{ number_format($price, 2) }}</td>
                        <td class="text-end fw-bold fs-5 text-dark">₱{{ number_format($totalVal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Notes Section -->
        <div class="mb-5 p-3 bg-light rounded border">
            <strong class="small text-muted text-uppercase d-block mb-1">Audit Notes / Justification:</strong>
            <p class="mb-0 text-secondary">{{ $transaction->notes ?: 'No additional notes recorded for this movement.' }}</p>
        </div>

        <!-- Signature Sign-Off Block -->
        <div class="row pt-4">
            <div class="col-6 text-center">
                <div style="border-bottom: 1px solid #333; height: 50px;"></div>
                <p class="small text-muted mt-1 mb-0">
                    <strong>{{ $transaction->user?->name ?? 'Encoder' }}</strong><br>
                    Encoded By (Staff / Authorized Agent)
                </p>
            </div>
            <div class="col-6 text-center">
                <div style="border-bottom: 1px solid #333; height: 50px;"></div>
                <p class="small text-muted mt-1 mb-0">
                    <strong>Inventory / Warehouse Supervisor</strong><br>
                    Verified & Audited By
                </p>
            </div>
        </div>
    </div>
</body>
</html>
