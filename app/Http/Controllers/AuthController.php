<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Debug logging
        \Log::info('Login attempt', [
            'email' => $credentials['email'],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Check if user exists
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        \Log::info('User lookup', [
            'email' => $credentials['email'],
            'found' => $user ? 'yes' : 'no',
            'role' => $user ? $user->role : 'N/A',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            \Log::info('Login successful', ['email' => $credentials['email']]);
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Redirect based on user role
            if ($user->isSupplier()) {
                return redirect()->intended(route('supplier.dashboard'));
            } else if ($user->isInternalAdmin()) {
                return redirect()->intended(route('dashboard'));
            }
            
            return redirect()->intended(route('dashboard'));
        }

        \Log::warning('Login failed - credentials invalid', [
            'email' => $credentials['email'],
            'user_exists' => $user ? 'yes' : 'no',
        ]);

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
