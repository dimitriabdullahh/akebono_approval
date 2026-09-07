<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $role
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Split multiple roles jika ada (e.g., "admin_qa|admin_purchasing")
        $allowedRoles = explode('|', $role);

        if (!in_array($user->role, $allowedRoles)) {
            abort(403, 'Anda tidak memiliki akses untuk halaman ini');
        }

        return $next($request);
    }
}
