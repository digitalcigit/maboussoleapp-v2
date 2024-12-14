<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\LoginResponse as BaseLoginResponse;
use Illuminate\Support\Facades\Log;

class LoginResponse extends BaseLoginResponse
{
    public function toResponse($request)
    {
        // Log des informations de débogage
        Log::debug('Tentative de connexion', [
            'email' => $request->input('email'),
            'authenticated' => auth()->check(),
            'session' => session()->all(),
        ]);

        return parent::toResponse($request);
    }
}
