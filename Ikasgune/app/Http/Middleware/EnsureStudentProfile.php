<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentProfile
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user?->isStudent() && ! $user->hasStudentProfile()
            && $request->routeIs('inicio', 'courses.*', 'enrollments.*', 'dashboard', 'materials.*')) {
            return redirect()->route('profile.edit');
        }

        return $next($request);
    }
}
