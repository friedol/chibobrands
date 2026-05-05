<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetChannel
{
    public function handle(Request $request, Closure $next, string $channel)
    {
        // share channel to views and mark on request
        app('view')->share('channel', $channel);
        $request->attributes->set('channel', $channel);
        return $next($request);
    }
}


