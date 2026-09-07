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
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->isSupplier()) {
            abort(403, 'Hanya supplier yang dapat mengakses halaman ini');
        }

        // Pastikan supplier memiliki relasi dengan supplier
        if (!$user->supplier_id || !$user->supplier) {
            abort(403, 'Data supplier tidak ditemukan untuk user ini');
        }

        return $next($request);
    }
}
