<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HikvisionTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = config('hikvision.sync_token');

        if (empty($configuredToken)) {
            Log::critical('HIKVISION_SYNC_TOKEN is not configured — all sync requests are blocked.');
            return response()->json(['error' => 'Integration not configured.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $provided = $request->bearerToken();

        if (empty($provided) || !hash_equals($configuredToken, $provided)) {
            Log::warning('Hikvision sync: invalid token attempt.', [
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return response()->json(['error' => 'Unauthorized.'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
