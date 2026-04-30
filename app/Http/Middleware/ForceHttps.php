<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceHttps
{
    public function handle(Request $request, Closure $next)
    {
        // paksa semua request dianggap HTTPS
        $request->server->set('HTTPS', 'on');

        return $next($request);
    }
}