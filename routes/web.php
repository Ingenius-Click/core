<?php

use Illuminate\Support\Facades\Route;
use Ingenius\Core\Http\Controllers\AuthController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

Route::prefix('central')->group(function () {});
