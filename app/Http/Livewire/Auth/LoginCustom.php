<?php

namespace App\Http\Livewire\Auth;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;

class LoginCustom extends BaseLogin
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('username')
                    ->label('Nombre de usuario')
                    ->required()
                    ->autofocus()
                    ->autocomplete('username')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->required()
                    ->autocomplete('current-password')
                    ->columnSpanFull(),
            ]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }
}
