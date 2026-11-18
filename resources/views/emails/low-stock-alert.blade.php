<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Low Stock Alert</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #2c3e50;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
        <div style="background-color: #f39c12; padding: 24px 30px; color: #ffffff;">
            <h2 style="margin: 0; font-size: 20px;">Gym Inventory Alert</h2>
            <p style="margin: 5px 0 0; opacity: 0.9; font-size: 14px;">Product Reached Reorder Threshold</p>
        </div>
        <div style="padding: 30px;">
            <h3 style="margin-top: 0; color: #212529;">Warning: Low Inventory for {{ $product->name }}</h3>
            <p>The on-hand inventory for this product has fallen to or below the safety reorder point.</p>

            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 8px 0; color: #6c757d;">Product Name:</td>
                    <td style="padding: 8px 0; font-weight: 600;">{{ $product->name }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 8px 0; color: #6c757d;">SKU Code:</td>
                    <td style="padding: 8px 0; font-family: monospace;">{{ $product->sku }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 8px 0; color: #6c757d;">Current Quantity:</td>
                    <td style="padding: 8px 0; font-weight: 700; color: #e74c3c; font-size: 16px;">{{ $product->quantity }} units</td>
                </tr>
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 8px 0; color: #6c757d;">Reorder Level:</td>
                    <td style="padding: 8px 0;">{{ $product->reorder_level }} units</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #6c757d;">Preferred Vendor:</td>
                    <td style="padding: 8px 0;">{{ $product->supplier_name }}</td>
                </tr>
            </table>

            <div style="margin-top: 30px; text-align: center;">
                <a href="{{ route('purchase-orders.create', ['product_id' => $product->id]) }}" style="background-color: #f39c12; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;">Reorder Now</a>
            </div>
        </div>
    </div>
</body>
</html>
