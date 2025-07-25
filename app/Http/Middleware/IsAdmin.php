<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Debug middleware
        \Log::info('IsAdmin middleware check', [
            'is_authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'user_role' => Auth::check() ? Auth::user()->role : 'not_authenticated',
            'request_url' => $request->url()
        ]);

        if (! Auth::check()) {
            \Log::warning('IsAdmin: User not authenticated');
            return redirect()->route('login.form');
        }

        if (Auth::user()->role !== 'admin') {
            \Log::warning('IsAdmin: User is not admin', [
                'user_role' => Auth::user()->role,
                'user_id' => Auth::id()
            ]);
            abort(403, 'Unauthorized access');
        }

        \Log::info('IsAdmin: Access granted');
        return $next($request);
    }
}