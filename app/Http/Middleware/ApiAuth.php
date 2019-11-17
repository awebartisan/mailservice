<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $request->header('signature')) {
            throw new AccessDeniedHttpException('Request does not contains authentication signature');
        }

        if ($request->header('signature') !== env('AUTH_SIGNATURE')) {
            throw new UnauthorizedHttpException("Unauthorized!");
        }

        return $next($request);
    }
}
