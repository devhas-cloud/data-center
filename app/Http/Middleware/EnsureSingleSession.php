<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EnsureSingleSession
{
     public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $exists = DB::table('sessions')
                ->where('id', session()->getId())
                ->where('user_id', Auth::id())
                ->exists();

            if (!$exists) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();

                return redirect()->route('login')
                ->withErrors([
                    'status' => 'Your session has expired or you have logged in from another device.',
                ]);
            }
        }
        return $next($request);
    }
}
