<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'message'    => $n->message,
                'url'        => $n->url,
                'is_read'    => $n->is_read,
                'created_at' => $n->created_at->diffForHumans(),
            ]);

        $unread = Notification::where('user_id', auth()->id())->where('is_read', false)->count();

        return response()->json(['data' => $notifications, 'unread' => $unread]);
    }

    public function markRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $notification->update(['is_read' => true]);
        return response()->json(['message' => 'OK']);
    }

    public function markAllRead()
    {
        Notification::where('user_id', auth()->id())->where('is_read', false)->update(['is_read' => true]);
        return response()->json(['message' => 'OK']);
    }
}
