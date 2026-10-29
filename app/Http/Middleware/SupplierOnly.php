<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SupplierOnly
{
    /**
     * Handle an incoming request.
     * Middleware untuk memastikan hanya supplier yang dapat mengakses route tertentu.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('info', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();

        if (!$user->isSupplier()) {
            abort(403, 'Hanya supplier yang dapat mengakses halaman ini');
        }

        // CRITICAL: Pastikan supplier memiliki relasi dengan supplier
        if (!$user->supplier_id) {
            \Log::error('SupplierOnly Middleware: User has no supplier_id', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]);
            
            // Force logout
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->with('error', 'Akun supplier Anda belum terhubung dengan data supplier. Silakan hubungi administrator.');
        }
        
        // Load supplier relationship if not loaded
        if (!$user->relationLoaded('supplier')) {
            $user->load('supplier');
        }
        
        // Verify supplier exists in database
        if (!$user->supplier) {
            \Log::error('SupplierOnly Middleware: Supplier relationship not found', [
                'user_id' => $user->id,
                'email' => $user->email,
                'supplier_id' => $user->supplier_id
            ]);
            
            // Force logout
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->with('error', 'Data supplier tidak ditemukan. Silakan hubungi administrator.');
        }

        return $next($request);
    }
}
