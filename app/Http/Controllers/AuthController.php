<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Login;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller implements HasMiddleware
{
    /**
     * Define the middleware for the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api', except: ['login','showLoginForm', 'showRegistrationForm']),
        ];
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Check if user exists and is active
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || $user->status !== 'active') {
            return response()->json(['error' => 'Account not active'], 401);
        }

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $this->logLoginTime($user->id);

        return $this->respondWithToken($token);
    }

    private function logLoginTime($userId)
    {
        // Get the current time and date
        $currentTime = now();
        $currentDate = $currentTime->toDateString();
        $timeIn = $currentTime->format('H:i:s');
    
        // Define 8 AM and 4 AM for comparison
        $eightAM = \Carbon\Carbon::createFromTime(8, 0, 0);
        $fourAM = \Carbon\Carbon::createFromTime(4, 0, 0);
        $timeInCarbon = \Carbon\Carbon::createFromFormat('H:i:s', $timeIn);
    
        // Check if the login time is valid (after 4 AM)
        if ($timeInCarbon->isBefore($fourAM)) {
            // If the login time is before 4 AM, do not record it
            return; // Exit the function early
        }
    
        // Initialize allowance variables
        $allowanceMinutes = 0;
        $allowanceHours = 0;
    
        // Determine the status and allowance
        if ($timeInCarbon->isBefore($eightAM)) {
            $status = 'early';
            $allowanceMinutes = $eightAM->diffInMinutes($timeInCarbon);
        } elseif ($timeInCarbon->eq($eightAM)) {
            $status = 'exactly';
            $allowanceMinutes = 0;
        } else { // After 8 AM
            $status = 'late';
            // Calculate how late the user is
            $allowanceHours = $eightAM->diffInHours($timeInCarbon); // Get the hours late
            $allowanceMinutes = $eightAM->diffInMinutes($timeInCarbon) % 60; // Get the remaining minutes
        }
    
        // Ensure allowance hours and minutes are non-negative
        $allowanceHours = max(0, $allowanceHours);
        $allowanceMinutes = max(0, $allowanceMinutes);
    
        // Format allowance as HH:MM:SS
        $allowanceFormatted = sprintf('%02d:%02d:00', $allowanceHours, $allowanceMinutes);
    
    // Determine the score based on the allowance
    if ($status === 'exactly') {
        $score = 3; // Score for logging in exactly at 8:00 AM
    } elseif ($status === 'late') {
        // Adjusted scoring logic based on total minutes
        if ($allowanceHours === 0 && $allowanceMinutes <= 30) {
            $score = 2; // Score if late but within 30 minutes
        } else {
            $score = 1; // Score if late and more than 30 minutes
        }
    } else { // Early
        $score = 3; // Assuming you want 3 points for being early
    }
    
        // Check if a record for today already exists for this user
        $existingLogin = Login::where('user_id', $userId)
            ->where('date', $currentDate)
            ->first();
    
        // If no record exists, create a new one
        if (!$existingLogin) {
            Login::create([
                'user_id' => $userId,
                'time_in' => $timeIn,
                'date' => $currentDate,
                'status' => $status,
                'allowance' => $allowanceFormatted, // Store formatted allowance correctly
                'score' => $score,
                'validation' => null, // Assuming you may have some validation logic later
                'validated_by' => null, // Assuming you may have this later too
            ]);
        }
    }
    
    

    public function me()
    {
        return response()->json(JWTAuth::user());
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out']);
    }
    public function refresh()
    {
        // Refresh the JWT token
        $newToken = JWTAuth::refresh();
    
        // Return the refreshed token response
        return $this->respondWithToken($newToken);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user_type' => JWTAuth::user()->user_type, // Retrieve authenticated user type
        ]);
    }

    public function showLoginForm()
    {
        // Check if there is no token
        if (!JWTAuth::getToken()) {
            // If no token is provided, return the login message
            return response()->json([
                'message' => 'Guest, please log in',
            ], 200); // Success response for unauthenticated users
        }
    
        // If a token is provided, attempt to authenticate the user
        try {
            // Attempt to authenticate the user with JWT
            if ($user = JWTAuth::authenticate(JWTAuth::getToken())) {
                $redirectTo = '/dashboard'; // Default redirection
    
                // Customize redirection based on the user type
                if ($user->user_type === 'admin') {
                    $redirectTo = '/admin';
                } elseif ($user->user_type === 'employee') {
                    $redirectTo = '/employee';
                } elseif ($user->user_type === 'chief') {
                    $redirectTo = '/chief';
                }
    
                return response()->json([
                    'message' => 'Already authenticated',
                    'redirect_to' => $redirectTo
                ], 200); // HTTP 200 means OK
            }
        } catch (JWTException $e) {
            // Handle token-related exceptions
            return response()->json(['message' => 'Token is invalid or expired'], 401);
        }
    }

    public function showRegistrationForm()
    {
        // Check if there is no token
        if (!JWTAuth::getToken()) {
            // If no token is provided, return the login message
            return response()->json([
                'message' => 'Guest, register',
            ], 200); // Success response for unauthenticated users
        }
    
        // If a token is provided, attempt to authenticate the user
        try {
            // Attempt to authenticate the user with JWT
            if ($user = JWTAuth::authenticate(JWTAuth::getToken())) {
                $redirectTo = '/dashboard'; // Default redirection
    
                // Customize redirection based on the user type
                if ($user->user_type === 'admin') {
                    $redirectTo = '/admin';
                } elseif ($user->user_type === 'employee') {
                    $redirectTo = '/employee';
                } elseif ($user->user_type === 'chief') {
                    $redirectTo = '/chief';
                }
    
                return response()->json([
                    'message' => 'Already authenticated',
                    'redirect_to' => $redirectTo
                ], 200); // HTTP 200 means OK
            }
        } catch (JWTException $e) {
            // Handle token-related exceptions
            return response()->json(['message' => 'Token is invalid or expired'], 401);
        }
    }

    
    
}