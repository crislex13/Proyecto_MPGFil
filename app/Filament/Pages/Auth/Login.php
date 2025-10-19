<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    /** Clave para forzar refresco de la imagen (cache-busting) */
    public int $captchaKey = 0;

    public function mount(): void
    {
        parent::mount();
        $this->refreshCaptcha();
    }

    public function refreshCaptcha(): void
    {
        // cambia el query param ?r= para que el <img> no quede cacheado
        $this->captchaKey = (int) now()->format('Uu');
        data_set($this->data, 'captcha', null);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('username')
                ->label('Nombre de usuario')
                ->placeholder('Ej: admin_258877')
                ->required()
                ->autofocus()
                ->autocomplete('username'),

            TextInput::make('password')
                ->label('Contraseña')
                ->password()
                ->required()
                ->autocomplete('current-password'),

            // Imagen del CAPTCHA
            Placeholder::make('captcha_image')
                ->label('Verificación')
                ->content(fn () => new HtmlString(
                    '<img src="'.captcha_src('flat').'&r='.$this->captchaKey.
                    '" alt="CAPTCHA" style="height:48px;border-radius:8px;border:1px solid #4b5563;background:#fff;padding:4px;">'
                )),

            // Botón para refrescar imagen
            Actions::make([
                Action::make('refresh_captcha')
                    ->label('Actualizar verificación')
                    ->icon('heroicon-m-arrow-path')
                    ->action(fn () => $this->refreshCaptcha()),
            ])->columnSpanFull(),

            // Input para escribir el texto del captcha
            TextInput::make('captcha')
                ->label('Escribe las letras de la imagen')
                ->placeholder('Sensible a letras/números')
                ->required(),
        ];
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['username'] ?? null,
            'password' => $data['password'] ?? null,
        ];
    }

    public function authenticate(): LoginResponse
    {
        // Valida contra el preset "flat" (igual al usado en captcha_src('flat'))
        $this->validate([
            'data.captcha' => ['required', 'captcha:flat'],
        ], [
            'data.captcha.required' => 'Confirma que no eres un robot.',
            'data.captcha.captcha'  => 'El texto no coincide. Intenta nuevamente.',
        ]);

        return parent::authenticate();
    }
}
