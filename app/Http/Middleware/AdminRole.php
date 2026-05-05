<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Check default guard first (for users table)
        $user = Auth::user();
        
        // If not found, check admin guard (for admins table)
        if (!$user) {
            $user = Auth::guard('admin')->user();
        }
        
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in to access this page.');
        }
        
        // If no roles specified, just check if user is authenticated
        if (empty($roles)) {
            return $next($request);
        }
        
        // Check if user has any of the required roles
        foreach ($roles as $role) {
            if ($user->role === $role) {
                return $next($request);
            }
        }
        
        // Operator can act as both designer and receptionist
        if ($user->role === 'operator') {
            if (in_array('designer', $roles) || in_array('receptionist', $roles)) {
                return $next($request);
            }
        }
        
        // If user is a super admin, allow access regardless of role restrictions
        if ($user->role === 'super_admin') {
            return $next($request);
        }
        
        // If we get here, the user doesn't have the required role
        return redirect()->route('admin.dashboard')
            ->with('error', 'You are not authorized to access this page.');
    }
}
