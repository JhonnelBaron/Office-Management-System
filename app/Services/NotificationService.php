<?php

namespace App\Services;

use App\Models\Api\Routeslip;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class NotificationService
{
public function markAsRead($id)
{
    try {
        $user = JWTAuth::user();

        // 1. UPDATE ROUTESLIPS TABLE
        // Siguraduhin nating magiging 1 ang is_read para hindi na siya bumalik sa "Unread"
        Routeslip::where('id', $id)->update(['is_read' => 1]);

        // 2. UPDATE NOTIFICATIONS TABLE
        // Hahanapin natin yung notification record base sa routeslip id sa loob ng JSON data
        $notification = $user->unreadNotifications()
            ->where('data->id', (int)$id)
            ->first();

        if ($notification) {
            // Ito ay default Laravel method na nag-uupdate ng `read_at` timestamp
            $notification->markAsRead();
        }

        return [
            'status' => 200,
            'message' => 'Database synced: Routeslip marked as read.',
            'unread_count' => $user->unreadNotifications()->count()
        ];
    } catch (JWTException $e) {
        return [
            'status' => 500,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

}