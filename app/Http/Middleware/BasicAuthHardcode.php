<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BasicAuthHardcode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Hardcoded username dan password
        $username = 'anambas';
        $password = 'brks123#';
        if ($request->getUser() !== $username || $request->getPassword() !== $password) {
            return response()->json([
              'code' => 401,
              'message' => 'Unauthorized',
            ],401);
        }
        return $next($request);
    }
}
