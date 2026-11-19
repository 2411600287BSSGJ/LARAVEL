<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sales Invoice #{{ $salesOrder->order_number }} - Gym Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffffff;
            color: #212529;
            font-size: 13px;
        }
        .invoice-card {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }
        @media print {
            body { margin: 0; padding: 0; }
            .invoice-card { border: none; padding: 0; max-width: 100%; margin: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="container my-3">
        <div class="no-print d-flex justify-content-between align-items-center mb-3 max-width-800 mx-auto" style="max-width: 800px;">
            <a href="{{ route('sales-orders.show', $salesOrder) }}" class="btn btn-outline-secondary btn-sm">
                &larr; Back to Order
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                Print Invoice
            </button>
        </div>

        <div class="invoice-card shadow-sm">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
                <div>
                    <h2 class="fw-bold text-danger mb-0">🏋️ Gym Inventory System</h2>
                    <p class="text-muted small mb-0">Fitness Equipment & Nutrition Distribution</p>
                    <p class="text-muted small mb-0">Manila, Philippines | Official Sales Order Voucher</p>
                </div>
                <div class="text-end">
                    <h4 class="fw-bold text-dark mb-1">SALES INVOICE</h4>
                    <div class="fw-bold text-secondary">#{{ $salesOrder->order_number }}</div>
                    <div class="small text-muted">Date: {{ $salesOrder->created_at->format('M d, Y') }}</div>
                    <span class="badge bg-{{ $salesOrder->status_badge_color }} text-uppercase mt-1">
                        {{ ucfirst($salesOrder->status) }}
                    </span>
                </div>
            </div>

            <!-- Customer & Order Meta -->
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="p-3 bg-light rounded h-100">
                        <h6 class="text-uppercase text-muted fw-bold small mb-2">Billed To (Customer):</h6>
                        <div class="fw-bold fs-6">{{ $salesOrder->customer_name }}</div>
                        @if($salesOrder->customer_phone)
                            <div class="small text-secondary">Phone: {{ $salesOrder->customer_phone }}</div>
                        @endif
                        @if($salesOrder->customer_email)
                            <div class="small text-secondary">Email: {{ $salesOrder->customer_email }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 bg-light rounded h-100">
                        <h6 class="text-uppercase text-muted fw-bold small mb-2">Order Information:</h6>
                        <div class="small"><span class="text-muted">Order Date:</span> {{ $salesOrder->created_at->format('M d, Y h:i A') }}</div>
                        <div class="small"><span class="text-muted">Created By:</span> {{ $salesOrder->creator?->name ?? 'Staff' }}</div>
                        <div class="small"><span class="text-muted">Approved By:</span> {{ $salesOrder->approvedBy?->name ?? 'Awaiting Approval' }}</div>
                        @if($salesOrder->approved_at)
                            <div class="small"><span class="text-muted">Approved At:</span> {{ $salesOrder->approved_at->format('M d, Y h:i A') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Itemized Table -->
            <table class="table table-bordered mb-4">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Item Description</th>
                        <th>SKU</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-end">Unit Price (PHP)</th>
                        <th class="text-end">Total Amount (PHP)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>
                            <div class="fw-bold">{{ $salesOrder->product?->name ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $salesOrder->product?->description ?? '' }}</small>
                        </td>
                        <td><code>{{ $salesOrder->product?->sku ?? 'N/A' }}</code></td>
                        <td class="text-center fw-bold">{{ $salesOrder->quantity }}</td>
                        <td class="text-end">₱{{ number_format($salesOrder->unit_price, 2) }}</td>
                        <td class="text-end fw-bold text-dark">₱{{ number_format($salesOrder->total_amount, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Subtotal:</td>
                        <td class="text-end fw-bold">₱{{ number_format($salesOrder->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Tax / VAT (0%):</td>
                        <td class="text-end fw-bold">₱0.00</td>
                    </tr>
                    <tr class="table-light">
                        <td colspan="5" class="text-end fw-bold fs-6">Grand Total (PHP):</td>
                        <td class="text-end fw-bold fs-6 text-success">₱{{ number_format($salesOrder->total_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            @if($salesOrder->notes)
                <div class="mb-4">
                    <span class="fw-bold small text-muted">Notes:</span>
                    <p class="small text-secondary mb-0">{{ $salesOrder->notes }}</p>
                </div>
            @endif

            <!-- Signatures -->
            <div class="row pt-5 mt-4 text-center">
                <div class="col-4">
                    <div class="border-top border-dark pt-2 small fw-bold">
                        {{ $salesOrder->creator?->name ?? 'Prepared By' }}
                    </div>
                    <div class="text-muted small">Encoded By (Staff)</div>
                </div>
                <div class="col-4">
                    <div class="border-top border-dark pt-2 small fw-bold">
                        {{ $salesOrder->approvedBy?->name ?? 'Administrator' }}
                    </div>
                    <div class="text-muted small">Authorized Signatory (Admin)</div>
                </div>
                <div class="col-4">
                    <div class="border-top border-dark pt-2 small fw-bold">
                        {{ $salesOrder->customer_name }}
                    </div>
                    <div class="text-muted small">Customer / Member Received By</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
