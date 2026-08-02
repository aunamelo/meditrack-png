<?php

namespace App\Http\Controllers;

use App\Services\NotificationInboxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function markAsRead(DatabaseNotification $notification): RedirectResponse
    {
        $user = auth()->user();
        NotificationInboxService::markAsRead($user, $notification);

        $url = NotificationInboxService::actionUrl($notification);

        return $url
            ? redirect($url)
            : redirect()->back();
    }

    public function markAllAsRead(): RedirectResponse
    {
        NotificationInboxService::markAllAsRead(auth()->user());

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}
