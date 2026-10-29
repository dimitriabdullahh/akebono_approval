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
        // Get login field (could be NPK or email)
        $loginField = $request->input('email'); // Field name still 'email' for compatibility
        $password = $request->input('password');
        
        // Determine if this is NPK (no @ symbol) or email (has @ symbol)
        $isNPK = strpos($loginField, '@') === false;

        \Log::info('Login attempt', [
            'input' => $loginField,
            'type' => $isNPK ? 'NPK' : 'Email',
            'ip' => $request->ip(),
        ]);

        if ($isNPK) {
            // **EMPLOYEE LOGIN VIA NPK**
            $credentials = [
                'npk' => $loginField,
                'password' => $password,
            ];

            // Check if employee exists
            $employee = \App\Models\Employee::where('npk', $loginField)->first();

            if (!$employee) {
                \Log::warning('Employee not found', ['npk' => $loginField]);
                
                throw ValidationException::withMessages([
                    'email' => 'NPK tidak ditemukan.',
                ]);
            }

            if (!$employee->is_active) {
                \Log::warning('Employee inactive', ['npk' => $loginField]);
                
                throw ValidationException::withMessages([
                    'email' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
                ]);
            }

            // Attempt authentication with employee guard
            if (Auth::guard('employee')->attempt($credentials, $request->boolean('remember'))) {
                \Log::info('Employee login successful', [
                    'npk' => $loginField,
                    'name' => $employee->name,
                    'division' => $employee->division,
                    'role' => $employee->role,
                ]);

                \Log::info('Regenerating session...');
                $request->session()->regenerate();
                
                \Log::info('Session regenerated, checking authentication...');
                $authCheck = Auth::guard('employee')->check();
                \Log::info('Auth check after regenerate', ['authenticated' => $authCheck]);
                
                if ($authCheck) {
                    $authenticatedEmployee = Auth::guard('employee')->user();
                    \Log::info('Authenticated employee details', [
                        'id' => $authenticatedEmployee->id,
                        'npk' => $authenticatedEmployee->npk,
                        'name' => $authenticatedEmployee->name,
                    ]);
                }
                
                \Log::info('Redirecting to dashboard...');
                
                // Redirect to dashboard
                return redirect()->intended(route('dashboard'));
            }

            \Log::warning('Employee login failed - wrong password', ['npk' => $loginField]);

            throw ValidationException::withMessages([
                'email' => 'Password salah.',
            ]);

        } else {
            // **SUPPLIER LOGIN VIA EMAIL**
            $credentials = [
                'email' => $loginField,
                'password' => $password,
            ];

            \Log::info('Supplier login attempt', ['email' => $loginField]);

            // Check if user exists
            $user = \App\Models\User::where('email', $loginField)->first();

            if (!$user) {
                \Log::warning('User not found', ['email' => $loginField]);
                
                throw ValidationException::withMessages([
                    'email' => 'Email tidak ditemukan.',
                ]);
            }

            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                \Log::info('Supplier login successful', [
                    'email' => $loginField,
                    'role' => $user->role,
                ]);
                
                $request->session()->regenerate();
                
                // Redirect based on user role
                if ($user->isSupplier()) {
                    // Validate supplier_id exists for supplier users
                    if (!$user->supplier_id || !$user->supplier) {
                        \Log::error('Supplier user missing supplier_id', [
                            'user_id' => $user->id,
                            'email' => $user->email,
                            'supplier_id' => $user->supplier_id
                        ]);
                        
                        Auth::logout();
                        $request->session()->invalidate();
                        
                        throw ValidationException::withMessages([
                            'email' => 'Akun Anda belum terhubung dengan data supplier. Silakan hubungi administrator.',
                        ]);
                    }
                    
                    return redirect()->intended(route('supplier.dashboard'));
                }
                
                // For any other role, go to dashboard
                return redirect()->intended(route('dashboard'));
            }

            \Log::warning('Supplier login failed - wrong password', ['email' => $loginField]);

            throw ValidationException::withMessages([
                'email' => 'Password salah.',
            ]);
        }
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
