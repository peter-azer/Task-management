<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        try {
            if (!$request->user() || !$request->user()->hasRole($role)) {
            return response()->json(
                $request->user()
            );
            }
        } catch (\Exception $e) {
            abort(500, 'An error occurred: ' . $e->getMessage());
        }

        return $next($request);
    }
}
