<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order Approved</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #2c3e50;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
        <div style="background-color: #27ae60; padding: 24px 30px; color: #ffffff;">
            <h2 style="margin: 0; font-size: 20px;">Gym Inventory Procurement</h2>
            <p style="margin: 5px 0 0; opacity: 0.85; font-size: 14px;">Purchase Order Approved</p>
        </div>
        <div style="padding: 30px;">
            <h3 style="margin-top: 0; color: #212529;">Order #{{ $purchaseOrder->reference }} has been Approved</h3>
            <p>Purchase order has been officially approved and is ready for vendor dispatch.</p>

            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 8px 0; color: #6c757d;">Product:</td>
                    <td style="padding: 8px 0; font-weight: 600;">{{ $purchaseOrder->product?->name }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 8px 0; color: #6c757d;">Quantity:</td>
                    <td style="padding: 8px 0; font-weight: 700;">{{ $purchaseOrder->quantity }} units</td>
                </tr>
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 8px 0; color: #6c757d;">Total Amount:</td>
                    <td style="padding: 8px 0; font-weight: 700; color: #27ae60;">₱{{ number_format($purchaseOrder->calculateTotal(), 2) }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 8px 0; color: #6c757d;">Supplier:</td>
                    <td style="padding: 8px 0;">{{ $purchaseOrder->supplier?->name ?? 'Unassigned' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #6c757d;">Approved By:</td>
                    <td style="padding: 8px 0;">{{ auth()->user()->name ?? 'Administrator' }}</td>
                </tr>
            </table>

            <div style="margin-top: 30px; text-align: center;">
                <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" style="background-color: #27ae60; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;">View Approved Order</a>
            </div>
        </div>
    </div>
</body>
</html>
