<?php

namespace App\Actions\Auth;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse;
use Illuminate\Http\RedirectResponse;

class CustomLogoutResponse implements LogoutResponse
{
    public function toResponse($request): RedirectResponse
    {
        return redirect('/admin/login'); // redirige manualmente al login
    }
}