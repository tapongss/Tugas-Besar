<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiCanDelete
{
    /**
     * Pastikan pemilik token API adalah administrator yang boleh menghapus data.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->can('delete-module')) {
            return response()->json([
                'message' => 'Hanya administrator yang memiliki izin untuk menghapus data modul.',
            ], 403);
        }

        return $next($request);
    }
}
