<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public PurchaseOrder $purchaseOrder,
        public string $action, // created, approved, rejected, sent, received, cancelled
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
            ->line("Purchase Order: {$this->purchaseOrder->reference}")
            ->line("Product: " . ($this->purchaseOrder->product?->name ?? 'Gym Equipment'))
            ->line("Quantity: {$this->purchaseOrder->quantity} units")
            ->action('View Purchase Order', route('purchase-orders.show', $this->purchaseOrder));

        if ($this->notes) {
            $mail->line("Note/Reason: {$this->notes}");
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'purchase_order',
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'purchase_order_id' => $this->purchaseOrder->id,
            'reference' => $this->purchaseOrder->reference,
            'action' => $this->action,
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'actor_role' => $this->actor->role,
            'product_name' => $this->purchaseOrder->product?->name ?? 'N/A',
            'quantity' => $this->purchaseOrder->quantity,
            'url' => route('purchase-orders.show', $this->purchaseOrder),
        ];
    }

    public function getTitle(): string
    {
        $role = ucfirst($this->actor->role ?? 'User');
        return match (strtolower($this->action)) {
            'created' => "New Purchase Order Created by {$role} {$this->actor->name}",
            'approved' => "Purchase Order Approved by {$role} {$this->actor->name}",
            'rejected' => "Purchase Order Rejected by {$role} {$this->actor->name}",
            'sent' => "Purchase Order Sent to Supplier by {$role} {$this->actor->name}",
            'received' => "Purchase Order Received by {$role} {$this->actor->name}",
            'cancelled' => "Purchase Order Cancelled by {$role} {$this->actor->name}",
            default => "Purchase Order Update by {$role} {$this->actor->name}",
        };
    }

    public function getMessage(): string
    {
        $actorDescription = ucfirst($this->actor->role) . " {$this->actor->name}";
        $productName = $this->purchaseOrder->product?->name ?? 'Product';
        $ref = $this->purchaseOrder->reference;

        return match (strtolower($this->action)) {
            'created' => "{$actorDescription} created Purchase Order [{$ref}] for {$this->purchaseOrder->quantity}x {$productName}.",
            'approved' => "{$actorDescription} officially approved Purchase Order [{$ref}].",
            'rejected' => "{$actorDescription} rejected Purchase Order [{$ref}]." . ($this->notes ? " Reason: {$this->notes}" : ''),
            'sent' => "{$actorDescription} marked Purchase Order [{$ref}] as sent to supplier.",
            'received' => "{$actorDescription} received shipment for Purchase Order [{$ref}]. Inventory has been updated.",
            'cancelled' => "{$actorDescription} cancelled Purchase Order [{$ref}]." . ($this->notes ? " Reason: {$this->notes}" : ''),
            default => "{$actorDescription} updated Purchase Order [{$ref}].",
        };
    }
}
