<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        $user = Auth::user();
        
        // Check if user has any of the required roles
        if (!in_array($user->role, $roles)) {
            // Log unauthorized access attempt
            \Illuminate\Support\Facades\Log::warning('Unauthorized access attempt', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'required_roles' => $roles,
                'url' => $request->url(),
                'ip' => $request->ip()
            ]);
            
            // Redirect based on user role with appropriate message
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard')->with('error', 'Access denied. Insufficient permissions.');
            } elseif (in_array($user->role, ['dosen', 'mahasiswa', 'user'])) {
                return redirect()->route('user.dashboard')->with('error', 'Access denied. Insufficient permissions.');
            } else {
                return redirect()->route('home')->with('error', 'Access denied. Invalid role.');
            }
        }

        return $next($request);
    }
}

/*
* Register this middleware in app/Http/Kernel.php:
* 
* protected $routeMiddleware = [
*     // ... other middleware
*     'role' => \App\Http\Middleware\CheckRole::class,
* ];
*/