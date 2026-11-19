<?php

namespace App\Notifications;

use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SalesOrderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public SalesOrder $salesOrder,
        public string $action, // created, approved, rejected, cancelled
        public User $actor,
        public ?string $notes = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->getTitle();
        $message = $this->getMessage();

        $mail = (new MailMessage)
            ->subject("[Gym Inventory] {$subject}")
            ->greeting("Hello {$notifiable->name},")
            ->line($message)
            ->line("Sales Order: {$this->salesOrder->order_number}")
            ->line("Customer: {$this->salesOrder->customer_name}")
            ->line("Product: " . ($this->salesOrder->product?->name ?? 'Gym Equipment'))
            ->line("Quantity: {$this->salesOrder->quantity} units")
            ->line("Total Amount: ₱" . number_format($this->salesOrder->total_amount, 2))
            ->action('View Sales Order', route('sales-orders.show', $this->salesOrder));

        if ($this->notes) {
            $mail->line("Note/Reason: {$this->notes}");
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'sales_order',
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'sales_order_id' => $this->salesOrder->id,
            'order_number' => $this->salesOrder->order_number,
            'action' => $this->action,
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'actor_role' => $this->actor->role,
            'customer_name' => $this->salesOrder->customer_name,
            'product_name' => $this->salesOrder->product?->name ?? 'N/A',
            'quantity' => $this->salesOrder->quantity,
            'total_amount' => $this->salesOrder->total_amount,
            'url' => route('sales-orders.show', $this->salesOrder),
        ];
    }

    public function getTitle(): string
    {
        $role = ucfirst($this->actor->role ?? 'User');
        return match (strtolower($this->action)) {
            'created' => "New Sales Order Awaiting Approval ({$role} {$this->actor->name})",
            'approved' => "Sales Order Approved by Admin {$this->actor->name}",
            'rejected' => "Sales Order Rejected by Admin {$this->actor->name}",
            'cancelled' => "Sales Order Cancelled by {$role} {$this->actor->name}",
            default => "Sales Order Update by {$role} {$this->actor->name}",
        };
    }

    public function getMessage(): string
    {
        $actorDescription = ucfirst($this->actor->role) . " {$this->actor->name}";
        $productName = $this->salesOrder->product?->name ?? 'Product';
        $orderNum = $this->salesOrder->order_number;
        $customer = $this->salesOrder->customer_name;

        return match (strtolower($this->action)) {
            'created' => "{$actorDescription} created Sales Order [{$orderNum}] for customer '{$customer}' ({$this->salesOrder->quantity}x {$productName}). Awaiting admin approval.",
            'approved' => "{$actorDescription} approved Sales Order [{$orderNum}]. Stock was deducted and order is fulfilled.",
            'rejected' => "{$actorDescription} rejected Sales Order [{$orderNum}]." . ($this->notes ? " Reason: {$this->notes}" : ''),
            'cancelled' => "{$actorDescription} cancelled Sales Order [{$orderNum}]." . ($this->notes ? " Reason: {$this->notes}" : ''),
            default => "{$actorDescription} updated Sales Order [{$orderNum}].",
        };
    }
}
