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

        // Generate a unique token
        // $token = JWTAuth::fromUser($user);
        // // $token = Str::random(60);
        // $user->remember_token = $token; // Store the token in the user's record
        // $user->save();

        // // Set the token in a cookie
        // cookie()->queue('remember_token', $token, 525600); 

           // Generate short-lived JWT token (access token)
           $accessToken = JWTAuth::fromUser($user);
        
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
                'user'=> $user,
                'role' => $role,
                'redirect_url' => $dashboardRoute,
                'access_token' => $accessToken
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
            'access_token' => $accessToken
        ], 200);
    }
}
