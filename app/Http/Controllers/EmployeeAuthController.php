<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class EmployeeAuthController extends Controller
{
    /**
     * Show employee login form
     */
    public function showLogin()
    {
        // If already authenticated as employee, redirect to existing dashboard
        if (Auth::guard('employee')->check()) {
            return redirect()->route('dashboard');
        }

        return view('employee.auth.login');
    }

    /**
     * Handle employee login with NPK
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'npk' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Log attempt
        Log::info('Employee login attempt', [
            'npk' => $credentials['npk'],
            'ip' => $request->ip(),
        ]);

        // Check if employee exists
        $employee = \App\Models\Employee::where('npk', $credentials['npk'])->first();
        
        if ($employee) {
            Log::info('Employee found', [
                'npk' => $credentials['npk'],
                'name' => $employee->name,
                'division' => $employee->division,
                'role' => $employee->role,
                'is_active' => $employee->is_active,
            ]);
        } else {
            Log::warning('Employee not found', ['npk' => $credentials['npk']]);
        }

        // Check if employee is active
        if ($employee && !$employee->is_active) {
            Log::warning('Employee login blocked - inactive account', [
                'npk' => $credentials['npk'],
            ]);

            throw ValidationException::withMessages([
                'npk' => 'Your account has been deactivated. Please contact administrator.',
            ]);
        }

        // Attempt authentication with employee guard
        if (Auth::guard('employee')->attempt($credentials, $request->boolean('remember'))) {
            Log::info('Employee login successful', [
                'npk' => $credentials['npk'],
                'name' => $employee->name,
            ]);

            $request->session()->regenerate();

            // Redirect to existing admin dashboard
            return redirect()->intended(route('dashboard'));
        }

        Log::warning('Employee login failed - invalid credentials', [
            'npk' => $credentials['npk'],
        ]);

        throw ValidationException::withMessages([
            'npk' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Handle employee logout
     */
    public function logout(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        
        if ($employee) {
            Log::info('Employee logout', [
                'npk' => $employee->npk,
                'name' => $employee->name,
            ]);
        }

        Auth::guard('employee')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('employee.login');
    }
}
