<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and is a student
        if (auth()->check() && auth()->user()->role === 'student') {
            return $next($request);
        }

        // Redirect to admin dashboard if not student
        return redirect()->route('admin.dashboard')->with('error', 'You do not have student access.');
    }
}
