<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Determine if the user is logged in to any of the given guards.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function authenticate($request, array $guards)
    {
        // If no guards specified, check both web and employee guards
        if (empty($guards)) {
            $guards = ['web', 'employee'];
        }

        // Check if authenticated on ANY of the guards
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Set the current guard as the default for this request
                Auth::shouldUse($guard);
                return;
            }
        }

        // If none of the guards have an authenticated user, throw exception
        $this->unauthenticated($request, $guards);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
