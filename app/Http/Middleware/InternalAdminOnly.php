<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InternalAdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        \Log::info('InternalAdminOnly middleware check', [
            'url' => $request->url(),
            'employee_check' => auth()->guard('employee')->check(),
            'user_check' => auth()->check(),
        ]);

        // Check employee authentication first (new system)
        if (auth()->guard('employee')->check()) {
            $employee = auth()->guard('employee')->user();
            
            \Log::info('Employee authenticated', [
                'npk' => $employee->npk,
                'name' => $employee->name,
                'is_active' => $employee->is_active,
            ]);
            
            // Check if employee is active
            if ($employee->is_active) {
                \Log::info('Employee is active, allowing access');
                return $next($request);
            }
            
            \Log::warning('Employee is inactive, logging out');
            
            // If inactive, logout and redirect
            auth()->guard('employee')->logout();
            $request->session()->invalidate();
            
            return redirect()->route('login')
                ->with('error', 'Your account has been deactivated.');
        }

        // Check legacy user authentication (if exists)
        if (auth()->check()) {
            $user = auth()->user();
            
            \Log::info('Legacy user authenticated', [
                'email' => $user->email,
                'role' => $user->role,
            ]);
            
            // Allow if user is internal admin
            if ($user->isInternalAdmin()) {
                \Log::info('Legacy user is internal admin, allowing access');
                return $next($request);
            }
        }

        \Log::warning('No valid authentication found, redirecting to login');

        // Not authenticated at all, redirect to login
        return redirect()->route('login')
            ->with('error', 'Please login to access this page.');
    }
}
