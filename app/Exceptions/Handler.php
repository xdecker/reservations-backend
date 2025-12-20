<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthenticationException) {

            // Si la petición es para Swagger o quiere JSON
            if ($request->wantsJson() || str_contains($request->path(), 'api/documentation')) {
                return response()->json([
                    'message' => 'Unauthenticated (Swagger safe)',
                ], 401);
            }

        }

        return parent::render($request, $exception);
    }
}
