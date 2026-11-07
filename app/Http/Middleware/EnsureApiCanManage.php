<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiCanManage
{
    /**
     * Pastikan pemilik token API memiliki hak kelola modul.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->can('manage-module')) {
            return response()->json([
                'message' => 'Akun ini tidak memiliki izin untuk mengubah data modul.',
            ], 403);
        }

        return $next($request);
    }
}
