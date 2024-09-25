<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRememberToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()){
            return $next($request);
        }

        $token = $request->cookie('remember_token');
        if ($token) {
            $user = User::where('remember_token', $token)->first();
            if ($user) {
                Auth::login($user);
            }
        }
        return $next($request);
    }
}
