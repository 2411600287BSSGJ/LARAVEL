<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order Sent</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #2c3e50;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
        <div style="background-color: #2980b9; padding: 24px 30px; color: #ffffff;">
            <h2 style="margin: 0; font-size: 20px;">Gym Inventory Procurement</h2>
            <p style="margin: 5px 0 0; opacity: 0.85; font-size: 14px;">Purchase Order Dispatched to Vendor</p>
        </div>
        <div style="padding: 30px;">
            <h3 style="margin-top: 0; color: #212529;">Purchase Order #{{ $purchaseOrder->reference }} Sent</h3>
            <p>This order has been officially sent to vendor <strong>{{ $purchaseOrder->supplier?->name }}</strong>. Expected fulfillment is within {{ $purchaseOrder->supplier?->lead_time_days ?? 7 }} business days.</p>

            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 8px 0; color: #6c757d;">Product:</td>
                    <td style="padding: 8px 0; font-weight: 600;">{{ $purchaseOrder->product?->name }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 8px 0; color: #6c757d;">Quantity:</td>
                    <td style="padding: 8px 0; font-weight: 700;">{{ $purchaseOrder->quantity }} units</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #6c757d;">Total Valuation:</td>
                    <td style="padding: 8px 0; font-weight: 700; color: #27ae60;">₱{{ number_format($purchaseOrder->calculateTotal(), 2) }}</td>
                </tr>
            </table>

            <div style="margin-top: 30px; text-align: center;">
                <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" style="background-color: #2980b9; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;">View Order Tracking</a>
            </div>
        </div>
    </div>
</body>
</html>
