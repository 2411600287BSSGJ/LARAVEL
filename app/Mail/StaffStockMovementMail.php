<?php

namespace App\Mail;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffStockMovementMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public InventoryTransaction $transaction,
        public Product $product,
        public User $staffUser,
        public User $recipient
    ) {}

    public function envelope(): Envelope
    {
        $typeLabel = match (strtolower($this->transaction->type)) {
            'stock_in', 'in', 'inbound', 'purchase' => 'Stock In',
            'stock_out', 'out', 'outbound', 'sale' => 'Stock Out',
            'adjustment' => 'Stock Adjustment',
            default => 'Stock Movement',
        };

        return new Envelope(
            subject: "[Gym Inventory] {$typeLabel} Alert: Recorded by Staff {$this->staffUser->name}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.staff-movement',
        );
    }
}
