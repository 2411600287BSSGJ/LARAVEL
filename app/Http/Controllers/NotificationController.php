<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Notifications\LowStockNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        $url = $notification->data['url'] ?? route('dashboard');
        return redirect($url)->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications for the current user as read.
     */
    public function markAllAsRead(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Get unread notifications in JSON.
     */
    public function unread(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifiedProductIds = $user->notifications()
            ->where('type', LowStockNotification::class)
            ->get(['data'])
            ->map(fn ($notification) => (int) ($notification->data['product_id'] ?? 0))
            ->filter()
            ->values();

        Product::query()
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->whereNotIn('id', $notifiedProductIds)
            ->each(fn (Product $product) => $user->notify(new LowStockNotification($product)));

        $unread = $request->user()->unreadNotifications()->latest()->take(10)->get();

        return response()->json([
            'count' => $user->unreadNotifications()->count(),
            'notifications' => $unread,
        ]);
    }
}
