<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth; // Import JWTAuth
use Pusher\Pusher;

class PusherController extends Controller
{
    public function auth(Request $request)
    {
        $channel_name = $request->channel_name;
        $socket_id = $request->socket_id;

        try {
            // Authenticate the user using JWT
            $user = JWTAuth::parseToken()->authenticate();

            if ($user) {
                $pusher = new Pusher(
                    env('PUSHER_APP_KEY'),
                    env('PUSHER_APP_SECRET'),
                    env('PUSHER_APP_ID'),
                    [
                        'cluster' => env('PUSHER_APP_CLUSTER'),
                        'useTLS' => true,
                    ]
                );

                // Authenticate the user for the given channel
                $auth = $pusher->socket_auth($channel_name, $socket_id);
                return response($auth, 200)->header('Content-Type', 'application/json');
            } else {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized: ' . $e->getMessage()], 403);
        }
    }
}
