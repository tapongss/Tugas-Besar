<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * Autentikasi REST API menggunakan Bearer token (hash SHA-256).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (blank($token)) {
            return response()->json([
                'message' => 'Token API diperlukan. Sertakan header Authorization: Bearer <token>.',
            ], 401);
        }

        $user = User::query()
            ->where('api_token', hash('sha256', $token))
            ->where('is_active', true)
            ->first();

        if (! $user) {
            return response()->json([
                'message' => 'Token API tidak valid atau akun tidak aktif.',
            ], 401);
        }

        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
