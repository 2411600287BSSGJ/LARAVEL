<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(public Product $product)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Low Stock Alert: {$this->product->name}")
            ->line("Product '{$this->product->name}' (SKU: {$this->product->sku}) is running low on stock.")
            ->line("Current Stock: {$this->product->quantity}")
            ->line("Reorder Level: {$this->product->reorder_level}")
            ->action('View Product', route('products.show', $this->product->id))
            ->line('Please initiate a purchase order or stock replenishment promptly.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'sku' => $this->product->sku,
            'current_quantity' => $this->product->quantity,
            'reorder_level' => $this->product->reorder_level,
            'message' => "Product '{$this->product->name}' (SKU: {$this->product->sku}) is running low! Current: {$this->product->quantity}, Reorder level: {$this->product->reorder_level}.",
            'suggested_action' => 'Reorder stock',
            'url' => route('products.show', $this->product->id),
        ];
    }
}
