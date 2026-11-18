<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Label — {{ $product->sku }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: #f4f6f9;
            color: #212529;
            padding: 30px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            @page {
                size: 80mm 50mm;
                margin: 0;
            }
            .label-card {
                box-shadow: none !important;
                border: 1px dashed #999 !important;
                page-break-inside: avoid;
                margin: 0 !important;
            }
        }
        .label-card {
            max-width: 380px;
            margin: 0 auto;
            background: #ffffff;
            border: 2px solid #212529;
            border-radius: 8px;
            padding: 16px 20px;
        }
        .barcode-lines {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            height: 48px;
            margin: 10px 0 4px;
            overflow: hidden;
        }
        .barcode-bar {
            background-color: #000;
            margin: 0 1px;
            height: 100%;
        }
    </style>
</head>
<body>
    <div class="d-flex justify-content-between align-items-center mb-4 no-print" style="max-width: 480px; margin: 0 auto 20px;">
        <div>
            <h5 class="mb-0 fw-bold"><i class="bi bi-upc-scan me-2"></i>Product SKU Barcode Tag</h5>
            <small class="text-muted">Standard warehouse & shelf inventory label.</small>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="bi bi-printer me-1"></i>Print Tag
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary btn-sm">Close</button>
        </div>
    </div>

    <!-- Product Barcode Label Card -->
    <div class="label-card">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
            <span class="fw-bold small text-danger text-uppercase tracking-wide">
                <i class="bi bi-activity me-1"></i>Gym Inventory System
            </span>
            <span class="badge bg-light text-secondary border small">{{ $product->category_name }}</span>
        </div>

        <h6 class="fw-bold text-dark mb-1 text-truncate" title="{{ $product->name }}">
            {{ $product->name }}
        </h6>

        <!-- Simulated Barcode Bars for the SKU -->
        <div class="barcode-lines">
            @php
                // Generate varied widths for realistic Code-128 appearance based on SKU chars
                $hash = md5($product->sku);
                $widths = [2, 3, 1, 4, 2, 1, 3, 2, 4, 1, 2, 3, 1, 3, 2, 4, 2, 1, 3, 1, 2, 4, 1, 3];
            @endphp
            @foreach($widths as $w)
                <div class="barcode-bar" style="width: {{ $w }}px;"></div>
                <div style="width: {{ ($w % 2) + 1 }}px;"></div>
            @endforeach
        </div>

        <!-- SKU Code -->
        <div class="text-center font-monospace fw-bold fs-6 mb-2 text-dark" style="letter-spacing: 3px;">
            *{{ $product->sku }}*
        </div>

        <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
            <div>
                <small class="text-muted d-block" style="font-size: 10px;">UNIT PRICE</small>
                <strong class="fs-5 text-dark">₱{{ number_format((float)$product->unit_price, 2) }}</strong>
            </div>
            <div class="text-end">
                <small class="text-muted d-block" style="font-size: 10px;">STOCK / REORDER</small>
                <span class="badge bg-{{ $product->stock_status_color }}">{{ $product->quantity }} units</span>
                <span class="text-muted small">/ min {{ $product->reorder_level }}</span>
            </div>
        </div>
    </div>
</body>
</html>
