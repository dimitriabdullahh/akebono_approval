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
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        $internalAdminRoles = [
            'admin_qa',
            'admin_purchasing',
            'admin_ppc',
        ];

        if (!in_array($user->role, $internalAdminRoles)) {
            abort(403, 'Hanya admin internal yang dapat mengakses halaman ini');
        }

        return $next($request);
    }
}
