<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Routeslip;
use App\Services\RouteslipService; // Import the service
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RouteSlipIntegrationController extends Controller
{
    protected $routeslipService;

    public function __construct(RouteslipService $routeslipService)
    {
        $this->routeslipService = $routeslipService;
    }

    public function handleUpdate(Request $request)
    {
        Log::info('--- API START: Incoming Routeslip (Service Mode) ---');

        try {
            $routeslip = $this->routeslipService->syncRouteslipData($request->all());

            return response()->json([
                'status' => 'success', 
                'message' => 'Routeslip synchronized successfully',
                'id' => $routeslip->id
            ], 200);

        } catch (\Exception $e) {
            Log::error('API ERROR in Service: ' . $e->getMessage());
            return response()->json([
                'status' => 'error', 
                'message' => 'Processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchRouteslips(Request $request)
    {
        $user = $request->user();

        // 1. Mark as read: I-update ang mga unread slips na pagmamay-ari ng user
        Routeslip::where('assigned_focal_user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // 2. Kunin ang normal na listahan gaya ng dati
        $routeslips = $this->routeslipService->getRouteslipsByRole($user);

        return response($routeslips, $routeslips['status'] ?? 200);
    }

    public function getNotificationCount(Request $request)
    {
        $user = $request->user();

        $unreadCount = $this->routeslipService->getPendingCount($user->id);

        $recentNotifications = Routeslip::where('assigned_focal_user_id', $user->id)
                                ->orderBy('is_read', 'asc') // Unread (0) muna bago Read (1)
                                ->orderBy('created_at', 'desc') // Pinakabago muna
                                ->limit(10) // Gawin nating 10 para sapat sa dropdown view
                                ->get();

        return response()->json([
            'count' => $unreadCount, // Badge (12)
            'data'  => $recentNotifications // Recent 10 (mixture of unread and latest read)
        ]);
    }
}