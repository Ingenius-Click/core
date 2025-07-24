<?php

namespace Ingenius\Core\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class MacrosServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Response::macro('api', function (string $message, mixed $data = null, int $code = 200, array $params = []) {
            return response()->json([
                'message' => $message,
                'data' => $data,
                ...$params
            ], $code);
        });
    }
}
