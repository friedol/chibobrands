<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        $allowedRoles = ['admin', 'saler', 'receptionist', 'designer', 'operator', 'super_admin', 'manager', 'delivery', 'gatekeeper', 'accountant'];
        
        if (!in_array($user->role, $allowedRoles) || !$user->verified) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Access denied. Admin privileges required.');
        }

        return $next($request);
    }
}
