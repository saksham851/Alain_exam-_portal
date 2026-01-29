<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and is a manager
        if (auth()->check() && auth()->user()->role === 'manager') {
            return $next($request);
        }

        // Redirect based on user role
        if (auth()->check()) {
            if (auth()->user()->role === 'admin') {
                return redirect()->route('admin.dashboard')->with('error', 'You do not have permission to access this route.');
            }
            if (auth()->user()->role === 'student') {
                return redirect()->route('student.dashboard')->with('error', 'You do not have permission to access this route.');
            }
        }

        return redirect()->route('login')->with('error', 'Please login first.');
    }
}
