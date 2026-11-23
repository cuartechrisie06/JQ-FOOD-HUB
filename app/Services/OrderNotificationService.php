<?php

namespace App\Services;

use App\Mail\OrderConfirmationMail;
use App\Mail\OrderStatusChangedMail;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class OrderNotificationService
{
    public function notifyAdminsOfNewOrder(Order $order): void
    {
        $this->notifyAdmins(
            $order,
            'New Order',
            'New order JQ-'.str_pad($order->order_id, 4, '0', STR_PAD_LEFT).' is pending.'
        );
    }

    public function notifyAdminsOfPendingPayment(Order $order): void
    {
        $this->notifyAdmins(
            $order,
            'Pending Payment',
            'A pending payment was submitted for order JQ-'.str_pad($order->order_id, 4, '0', STR_PAD_LEFT).'.'
        );
    }

    public function notifyStatusChanged(Order $order, string $previousStatus, string $newStatus): void
    {
        $notification = Notification::create([
            'user_id' => $order->user_id,
            'order_id' => $order->order_id,
            'type' => 'Status Change',
            'message' => "Order status changed from {$previousStatus} to {$newStatus}.",
            'is_read' => false,
            'email_sent' => false,
            'created_at' => now(),
        ]);

        $order->loadMissing(['user', 'calendar', 'booking']);
        if ($newStatus === 'Confirmed') {
            Mail::to($order->user->email)->queue(new OrderConfirmationMail($order));
        } else {
            Mail::to($order->user->email)->queue(new OrderStatusChangedMail($order, $previousStatus, $newStatus));
        }

        $notification->update(['email_sent' => true]);
    }

    private function notifyAdmins(Order $order, string $type, string $message): void
    {
        $adminRoleIds = Role::whereIn('role_name', ['Admin', 'Owner'])->pluck('role_id');
        $admins = User::whereIn('role_id', $adminRoleIds)
            ->where('status', 'Active')
            ->get();

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->user_id,
                'order_id' => $order->order_id,
                'type' => $type,
                'message' => $message,
                'is_read' => false,
                'email_sent' => false,
                'created_at' => now(),
            ]);
        }
    }
}
