<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $role)
    {

        $user = Auth::user();

        $timezone = session('timezone') ?? $user->timezone ?? 'UTC';

        if ($user && $user->role === $role) {
            
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
            return $next($request);
        }

        return redirect('/');
    }
}
