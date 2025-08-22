<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            \Illuminate\Support\Facades\Log::info('Auth exception for request: ' . $request->getUri());
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated',
                    'error' => 'authentication_required'
                ], 401);
            }
        });
        
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->is('api/*')) {
                // Don't handle validation exceptions - let them pass through normally
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return null; // Let Laravel handle validation exceptions normally
                }
                
                \Illuminate\Support\Facades\Log::error('API Exception: ' . $e->getMessage(), [
                    'url' => $request->getUri(),
                    'method' => $request->getMethod(),
                    'headers' => $request->headers->all(),
                    'exception' => get_class($e)
                ]);
                
                // Return JSON for other API errors
                return response()->json([
                    'message' => 'Server Error',
                    'error' => 'internal_server_error'
                ], 500);
            }
        });
    })->create();
