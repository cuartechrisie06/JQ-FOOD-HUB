<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()->notifications()
            ->with('order')
            ->orderByDesc('created_at')
            ->paginate(30);

        auth()->user()->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function markRead(Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 404);
        $notification->update(['is_read' => true]);

        return back()->with('status', 'Notification marked as read.');
    }
}
