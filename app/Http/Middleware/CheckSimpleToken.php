<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSimpleToken
{
    public function handle(Request $request, Closure $next)
    {
        $authorization = $request->header('Authorization'); 
        $expected = 'Bearer ' . env('JWT_SECRET');

        if ($authorization !== $expected) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
