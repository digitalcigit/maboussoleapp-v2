<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        $message = 'Tentative d\'authentification à '.date('Y-m-d H:i:s');
        file_put_contents(
            storage_path('logs/auth.log'),
            $message.PHP_EOL.json_encode([
                'is_json' => $request->expectsJson(),
                'path' => $request->path(),
                'method' => $request->method(),
                'auth_check' => auth()->check(),
                'user' => auth()->user(),
            ], JSON_PRETTY_PRINT).PHP_EOL,
            FILE_APPEND
        );

        return $request->expectsJson() ? null : route('filament.admin.auth.login');
    }
}
