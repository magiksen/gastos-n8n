<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VerifyApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-API-Token');

        $expected = (string) config('services.api_token', '');

        if (! $token || ! $expected || ! hash_equals($expected, $token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token de API inválido.',
            ], 401);
        }

        return $next($request);
    }
}
