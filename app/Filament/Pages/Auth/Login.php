<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\TextInput;

class Login extends BaseLogin
{
    protected function getFormSchema(): array
    {
        return [
            TextInput::make('username')
                ->label('Nombre de usuario')
                ->placeholder('Ej: admin_258877')
                ->type('text')
                ->inputMode('text')
                ->required()
                ->autofocus()
                ->autocomplete('username'),

            TextInput::make('password')
                ->label('Contraseña')
                ->password()
                ->required()
                ->autocomplete('current-password'),
        ];
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }
}