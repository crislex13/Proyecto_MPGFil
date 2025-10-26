<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\{TextInput, Placeholder, Actions, Actions\Action};
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class Login extends BaseLogin
{
    /** Cache-busting para la imagen del captcha */
    public int $captchaKey = 0;

    /** Límites (puedes sobreescribirlos en .env) */
    protected int $maxAttempts = 5;    // LOGIN_MAX_ATTEMPTS
    protected int $decayMinutes = 15;  // LOGIN_DECAY_MINUTES

    public function mount(): void
    {
        parent::mount();

        // Lee límites desde .env (con fallback)
        $this->maxAttempts  = (int) env('LOGIN_MAX_ATTEMPTS', 5);
        $this->decayMinutes = (int) env('LOGIN_DECAY_MINUTES', 15);

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
                    '<img src="' . captcha_src('flat') . '&r=' . $this->captchaKey .
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

    /** Clave única para rate limiting: username|ip */
    protected function throttleKey(): string
    {
        $username = (string) data_get($this->data, 'username', '');
        return Str::lower($username) . '|' . request()->ip();
    }

    public function authenticate(): LoginResponse
    {
        $key = $this->throttleKey();

        // 1) Si ya está bloqueado, dispara evento y muestra tiempo restante
        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            event(new Lockout(request()));
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'data.username' => __("Demasiados intentos. Inténtalo en :seg s.", ['seg' => $seconds]),
            ]);
        }

        // 2) Validación estricta en servidor (usuario/contraseña/captcha)
        $this->validate([
            'data.username' => ['required','string','min:4','max:50','regex:/^[A-Za-z0-9_.-]+$/'],
            'data.password' => ['required','string','min:6'],
            'data.captcha'  => ['required','captcha:flat'],
        ], [
            'data.username.required' => 'Ingresa tu usuario.',
            'data.username.regex'    => 'Solo letras, números y . _ -',
            'data.password.required' => 'Ingresa tu contraseña.',
            'data.password.min'      => 'La contraseña debe tener al menos 6 caracteres.',
            'data.captcha.required'  => 'Confirma que no eres un robot.',
            'data.captcha.captcha'   => 'El texto de verificación no coincide.',
        ]);

        try {
            // 3) Intento de login
            $response = parent::authenticate();

            // Éxito: limpia contador
            RateLimiter::clear($key);

            return $response;
        } catch (ValidationException $e) {
            // 4) Credenciales malas → incrementa contador y evalúa lockout
            RateLimiter::hit($key, $this->decayMinutes * 60);

            if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
                event(new Lockout(request())); // para tu bitácora (activitylog)
            }

            throw $e;
        }
    }
}
