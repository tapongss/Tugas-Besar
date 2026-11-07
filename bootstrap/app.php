<?php

use App\Http\Middleware\AuthenticateApiToken;
use App\Http\Middleware\EnsureApiCanManage;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'api.token' => AuthenticateApiToken::class,
            'api.can-manage' => EnsureApiCanManage::class,
            'api.json' => ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Laravel mengubah TokenMismatchException menjadi HttpException 419 sebelum
        // render callback berjalan, jadi kita cocokkan lewat status code.
        $exceptions->render(function (HttpException $exception, Request $request) {
            if ($exception->getStatusCode() !== 419) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi Anda telah berakhir. Muat ulang halaman lalu coba lagi.',
                ], 419);
            }

            // Sesi tidak dapat diandalkan pada jalur CSRF gagal (exception terjadi
            // sebelum StartSession menyimpan sesi), jadi penanda memakai query string.
            return redirect()->route('login', ['expired' => 1]);
        });
    })->create();
