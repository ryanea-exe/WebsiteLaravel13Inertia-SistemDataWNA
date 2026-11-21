<?php

use App\Http\Middleware\EnsureUserRole;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => EnsureUserRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->report(function (Throwable $e): void {
            try {
                $sqlState = null;
                $errorCode = $e->getCode();

                if ($e instanceof QueryException) {
                    $sqlState = $e->errorInfo[0] ?? (is_string($e->getCode()) ? $e->getCode() : null);
                    $errorCode = $e->errorInfo[1] ?? $e->getCode();
                } elseif ($e instanceof PDOException) {
                    $sqlState = $e->errorInfo[0] ?? (is_string($e->getCode()) ? $e->getCode() : null);
                    $errorCode = $e->errorInfo[1] ?? $e->getCode();
                }

                $payload = [
                    'exception_class' => get_class($e),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine(),
                    'error_code' => $errorCode,
                    'sql_state' => $sqlState,
                    'timestamp' => date('c'),
                ];

                error_log(sprintf('[GLOBAL_DIAGNOSTIC] %s', json_encode($payload)));
            } catch (Throwable) {
                // Diagnostic logging must not throw secondary exceptions
            }
        });
    })
    ->create();

/**
 * 🔥 Tambahkan ini untuk load .env dari folder bootstrap/db/
 */
$app->useEnvironmentPath(__DIR__);
$app->loadEnvironmentFrom('db/.env');

return $app;
