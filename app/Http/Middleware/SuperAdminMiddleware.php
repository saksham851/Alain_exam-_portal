<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role === 'superadmin') {
            return $next($request);
        }

        if (auth()->check()) {
            if (auth()->user()->role === 'admin') {
                return redirect()->route('admin.dashboard')->with('error', 'You do not have Super Admin permission.');
            }
            if (auth()->user()->role === 'manager') {
                return redirect()->route('manager.dashboard')->with('error', 'You do not have permission.');
            }
            if (auth()->user()->role === 'student') {
                return redirect()->route('student.dashboard')->with('error', 'You do not have permission.');
            }
        }

        return redirect()->route('login');
    }
}
