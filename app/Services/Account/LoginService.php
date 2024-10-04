<?php

namespace App\Services\Account;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

use Illuminate\Support\Facades\Hash;

class LoginService
{
    public function login(array $payload)
    {
        $user = User::where('email', $payload['email'])->first();

        if (!$user || !Hash::check($payload['password'], $user->password)) {
            return [
                'message' => 'Invalid credentials',
                'status' => 401
            ];
        } 

        if ($user->status !== 'active') {
            return [
                'message' => 'Account is not active',
                'status' => 403
            ];
        }
           // Generate short-lived JWT token (access token)
           try {
            $accessToken = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return [
                'message' => 'Could not create access token',
                'status' => 500
            ];
        }
        
           // Generate a long-lived refresh token
           $refreshToken = Str::random(60); // You can also use JWT for this if needed
   
           // Store refresh token in the database (or redis for scalability)
           $user->refresh_token = $refreshToken;
           $user->save();
   
           // Set refresh token in an HttpOnly cookie
           cookie()->queue(cookie('refresh_token', $refreshToken, 525600, null, null, true, true)); 
        
        switch ($user->user_type) {
            case 'admin':
                $role = 'admin';
                $dashboardRoute = '/admin';
                break;
            case 'chief':
                $role = 'chief';
                $dashboardRoute = '/chief';
                break;
            case 'employee':
                $role = 'employee';
                $dashboardRoute = '/employee';
                break;
            default:
                return [
                    'message' => 'Invalid user role',
                    'status' => 403
                ];
        }

        // Optionally, you can return the user details or a token for API-based login
        return [
            'data' => [
                'user'=> $user->makeHidden(['password', 'refresh_token']),
                'role' => $role,
                'redirect_url' => $dashboardRoute,
                'access_token' => $accessToken,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60
            ],
            'message' => 'Login successful',
            'status' => 200
        ];
    }

    public function refreshToken($refreshToken)
    {
        // Validate refresh token
        $user = User::where('refresh_token', $refreshToken)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid refresh token'], 401);
        }

        // Generate a new access token
        $accessToken = JWTAuth::fromUser($user);

        return response()->json([
            'access_token' => $accessToken,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60
        ], 200);
    }

    public function logout()
    {
        try {
            // Invalidate the JWT token
            JWTAuth::invalidate(JWTAuth::getToken());
    
            // Get the currently authenticated user
            $user = JWTAuth::user();
    
            // If the user is found, clear their refresh token
            if ($user) {
                $user->refresh_token = null;
                $user->save();
            }
    
            // Clear the refresh token cookie
            cookie()->queue(cookie()->forget('refresh_token'));
    
            // Return a success response
            return response()->json([
                'message' => 'Successfully logged out',
                'status' => 200
        ]);
        } catch (JWTException $e) {
            // Handle token invalidation errors
            return response()->json(['message' => 'Failed to log out, please try again'], 500);
        }
    }

}
