<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized access! Please log in.'], 401);
        }

        // Check if the user's role matches the required role
        if (Auth::user()->user_type !== $role) {
            return response()->json(['message' => 'Forbidden! You do not have permission to access this resource.'], 403);
        }

        // Proceed to the next middleware/request
        return $next($request);
    }
}
