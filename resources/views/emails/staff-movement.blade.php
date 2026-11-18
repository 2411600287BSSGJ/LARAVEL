<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Movement Notification</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 30px 15px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.08); border: 1px solid #e9ecef;">
        
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%); color: #ffffff; padding: 25px 30px; text-align: center;">
            <div style="font-size: 32px; margin-bottom: 5px;">🏋️</div>
            <h2 style="margin: 0; font-size: 20px; font-weight: 700; letter-spacing: 0.5px;">Gym Inventory System</h2>
            <p style="margin: 5px 0 0; color: #adb5bd; font-size: 13px; text-transform: uppercase; letter-spacing: 1px;">Audit Trail Notification</p>
        </div>

        @php
            $isStockIn = in_array(strtolower($transaction->type), ['stock_in', 'in', 'inbound', 'purchase']);
            $isStockOut = in_array(strtolower($transaction->type), ['stock_out', 'out', 'outbound', 'sale']);
            $badgeColor = $isStockIn ? '#27ae60' : ($isStockOut ? '#e74c3c' : '#f39c12');
            $typeLabel = $isStockIn ? 'Stock In (Replenishment)' : ($isStockOut ? 'Stock Out (Dispatch / Sale)' : 'Stock Adjustment');
            $sign = $isStockIn ? '+' : ($isStockOut ? '-' : ($transaction->quantity >= 0 ? '+' : ''));
            $price = $transaction->unit_price ?? $product->unit_price ?? 0;
            $totalVal = abs($transaction->quantity) * $price;
        @endphp

        <!-- Content Area -->
        <div style="padding: 30px;">
            <p style="font-size: 15px; color: #495057; margin-top: 0;">
                Hello <strong>{{ $recipient->name }}</strong>,
            </p>
            <p style="font-size: 14px; color: #6c757d; line-height: 1.6;">
                This is an automated notification that staff member <strong style="color: #212529;">{{ $staffUser->name }}</strong> ({{ $staffUser->email }}) has encoded an inventory movement into the system.
            </p>

            <!-- Status Banner -->
            <div style="background-color: #f8f9fa; border-left: 4px solid {{ $badgeColor }}; padding: 15px 20px; border-radius: 6px; margin: 20px 0;">
                <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; font-weight: 600;">Transaction Type</div>
                <div style="font-size: 18px; font-weight: 700; color: {{ $badgeColor }}; margin-top: 2px;">
                    {{ $typeLabel }}
                </div>
            </div>

            <!-- Details Table -->
            <table style="width: 100%; border-collapse: collapse; font-size: 14px; margin: 20px 0;">
                <tbody>
                    <tr style="border-bottom: 1px solid #f1f3f5;">
                        <td style="padding: 10px 0; color: #6c757d; width: 40%;">Product Name:</td>
                        <td style="padding: 10px 0; font-weight: 600; color: #212529;">{{ $product->name }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f3f5;">
                        <td style="padding: 10px 0; color: #6c757d;">SKU:</td>
                        <td style="padding: 10px 0; font-family: monospace; color: #212529;">{{ $product->sku }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f3f5;">
                        <td style="padding: 10px 0; color: #6c757d;">Quantity Moved:</td>
                        <td style="padding: 10px 0; font-weight: 700; color: {{ $badgeColor }}; font-size: 16px;">
                            {{ $sign }}{{ abs($transaction->quantity) }} units
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f3f5;">
                        <td style="padding: 10px 0; color: #6c757d;">Unit Price:</td>
                        <td style="padding: 10px 0; color: #212529;">₱{{ number_format($price, 2) }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f3f5;">
                        <td style="padding: 10px 0; color: #6c757d;">Total Value:</td>
                        <td style="padding: 10px 0; font-weight: 600; color: #27ae60;">₱{{ number_format($totalVal, 2) }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f3f5;">
                        <td style="padding: 10px 0; color: #6c757d;">Remaining Product Stock:</td>
                        <td style="padding: 10px 0; font-weight: 700; color: #212529;">{{ $product->quantity }} units</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f3f5;">
                        <td style="padding: 10px 0; color: #6c757d;">Reference Number:</td>
                        <td style="padding: 10px 0; font-family: monospace; color: #495057;">{{ $transaction->reference_number ?? $transaction->reference ?? '—' }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f3f5;">
                        <td style="padding: 10px 0; color: #6c757d;">Encoded By:</td>
                        <td style="padding: 10px 0; color: #212529;">{{ $staffUser->name }} (Staff)</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f3f5;">
                        <td style="padding: 10px 0; color: #6c757d;">Date & Time:</td>
                        <td style="padding: 10px 0; color: #495057;">{{ $transaction->created_at ? $transaction->created_at->format('M d, Y h:i:s A') : now()->format('M d, Y h:i:s A') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #6c757d; vertical-align: top;">Notes:</td>
                        <td style="padding: 10px 0; color: #495057; font-style: italic;">{{ $transaction->notes ?: 'No notes provided.' }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Button CTA -->
            <div style="text-align: center; margin: 30px 0 10px;">
                <a href="{{ route('transactions.show', $transaction->id) }}" style="display: inline-block; background-color: #c0392b; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: 600; font-size: 14px; box-shadow: 0 2px 6px rgba(192, 57, 43, 0.3);">
                    View in Audit Trail
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8f9fa; border-top: 1px solid #e9ecef; padding: 20px; text-align: center; font-size: 12px; color: #adb5bd;">
            <p style="margin: 0 0 5px;">This email was sent to {{ $recipient->email }} because you are registered as {{ ucfirst($recipient->role ?? 'staff') }} in the Gym Inventory System.</p>
            <p style="margin: 0;">&copy; {{ date('Y') }} Gym Inventory System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
