<?php

namespace App\Http\Livewire\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Form;
use Filament\Forms\Components\{TextInput, Placeholder, Actions, Actions\Action};
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;
use App\Models\ActivityLog;
use Filament\Notifications\Notification;


class LoginCustom extends BaseLogin
{
    public int $captchaKey = 0;
    protected int $maxAttempts = 5;    // .env LOGIN_MAX_ATTEMPTS
    protected int $decayMinutes = 15;  // .env LOGIN_DECAY_MINUTES

    public function mount(): void
    {
        parent::mount();
        $this->maxAttempts = (int) env('LOGIN_MAX_ATTEMPTS', 5);
        $this->decayMinutes = (int) env('LOGIN_DECAY_MINUTES', 15);
        $this->refreshCaptcha();
    }

    public function refreshCaptcha(): void
    {
        $this->captchaKey = (int) now()->format('Uu');
        data_set($this->data, 'captcha', null);
    }

    protected function throttleKey(): string
    {
        $username = (string) data_get($this->data, 'username', '');
        return Str::lower($username) . '|' . request()->ip();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('username')->label('Nombre de usuario')
                ->required()->autofocus()->autocomplete('username')
                ->rule('string')->rule('min:4')->rule('max:50')
                ->rule('regex:/^[A-Za-z0-9_.-]+$/'),

            TextInput::make('password')->label('Contraseña')
                ->password()->required()->autocomplete('current-password')
                ->rule('string')->rule('min:6'),

            Placeholder::make('captcha_image')->label('Verificación')->content(
                fn() => new HtmlString('<img src="' . captcha_src('flat') . '&r=' . $this->captchaKey .
                    '" alt="CAPTCHA" style="height:48px;border-radius:8px;border:1px solid #4b5563;background:#fff;padding:4px;">')
            ),

            Actions::make([
                Action::make('refresh_captcha')->label('Actualizar verificación')
                    ->icon('heroicon-m-arrow-path')->action(fn() => $this->refreshCaptcha()),
            ])->columnSpanFull(),

            TextInput::make('captcha')->label('Escribe las letras de la imagen')->required(),
        ]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return ['username' => $data['username'] ?? null, 'password' => $data['password'] ?? null];
    }

    public function authenticate(): LoginResponse
    {
        $key = $this->throttleKey();

        // 1) Si ya está bloqueado → evento + bitácora + toast + mensaje
        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            event(new Lockout(request()));

            ActivityLog::create([
                'log_name' => 'auth',
                'event' => 'lockout',
                'description' => 'Usuario bloqueado temporalmente',
                'properties' => [
                    'username' => (string) data_get($this->data, 'username'),
                    'ip' => request()->ip(),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
            ]);

            $seconds = RateLimiter::availableIn($key);

            // 🔔 Toast de bloqueo
            Notification::make()
                ->danger()
                ->title('Bloqueo temporal')
                ->body("Demasiados intentos. Inténtalo en {$seconds} s.")
                ->send();

            throw ValidationException::withMessages([
                'data.username' => __("Demasiados intentos. Inténtalo en :seg s.", ['seg' => $seconds]),
            ]);
        }

        // 2) Validación server-side
        $this->validate([
            'data.username' => ['required', 'string', 'min:4', 'max:50', 'regex:/^[A-Za-z0-9_.-]+$/'],
            'data.password' => ['required', 'string', 'min:6'],
            'data.captcha' => ['required', 'captcha:flat'],
        ], [
            'data.username.required' => 'Ingresa tu usuario.',
            'data.username.regex' => 'Solo letras, números y . _ -',
            'data.password.required' => 'Ingresa tu contraseña.',
            'data.password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'data.captcha.required' => 'Confirma que no eres un robot.',
            'data.captcha.captcha' => 'El texto de verificación no coincide.',
        ]);

        try {
            // 3) Autenticar
            $response = parent::authenticate();

            RateLimiter::clear($key);

            // 3a) Bitácora: login OK
            ActivityLog::create([
                'log_name' => 'auth',
                'event' => 'login',
                'description' => 'Inicio de sesión',
                'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
                'causer_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
            ]);

            // 🔔 Toast opcional de bienvenida
            Notification::make()
                ->success()
                ->title('Bienvenido')
                ->send();

            return $response;

        } catch (ValidationException $e) {
            // 4) Credenciales inválidas
            RateLimiter::hit($key, $this->decayMinutes * 60);

            ActivityLog::create([
                'log_name' => 'auth',
                'event' => 'login_failed',
                'description' => 'Intento de login fallido',
                'properties' => [
                    'username' => (string) data_get($this->data, 'username'),
                    'ip' => request()->ip(),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
            ]);

            if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
                event(new Lockout(request()));
                ActivityLog::create([
                    'log_name' => 'auth',
                    'event' => 'lockout',
                    'description' => 'Usuario bloqueado temporalmente',
                    'properties' => [
                        'username' => (string) data_get($this->data, 'username'),
                        'ip' => request()->ip(),
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => substr((string) request()->userAgent(), 0, 255),
                ]);
            }

            // 🔔 Toast de acceso denegado
            Notification::make()
                ->danger()
                ->title('Acceso denegado')
                ->body('Usuario o contraseña incorrectos.')
                ->send();

            // Banner superior + mensajes por campo
            session()->flash('auth_error_banner', 'Usuario o contraseña incorrectos.');
            throw ValidationException::withMessages([
                'data.username' => 'Usuario o contraseña incorrectos.',
                'data.password' => 'Usuario o contraseña incorrectos.',
            ]);
        }
    }


}
