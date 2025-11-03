<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LogoutTeknisi
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->hasRole('Teknisi')) {
                $lastActivity = session('last_activity_time');
                $now = Carbon::now();

                if ($lastActivity && $now->diffInSeconds($lastActivity) >= 30) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')->withErrors([
                        'session' => 'You have been logged out due to inactivity.',
                    ]);
                }

                session(['last_activity_time' => $now]);
            }
        }

        return $next($request);
    }
}