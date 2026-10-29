<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeOnly
{
    /**
     * Handle an incoming request.
     *
     * Ensure only authenticated employees can access the route
     * Redirect to employee login if not authenticated
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('employee')->check()) {
            return redirect()->route('employee.login')
                ->with('error', 'Please login to access this page.');
        }

        $employee = Auth::guard('employee')->user();

        // Check if employee is active
        if (!$employee->is_active) {
            Auth::guard('employee')->logout();
            $request->session()->invalidate();
            
            return redirect()->route('employee.login')
                ->with('error', 'Your account has been deactivated.');
        }

        return $next($request);
    }
}
