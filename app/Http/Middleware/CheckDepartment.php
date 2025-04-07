<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckDepartment
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $department)
    {
        $user = Auth::user();

        if (!$user || $user->department->name !== $department) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
