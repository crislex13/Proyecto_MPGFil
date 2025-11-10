<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FallbackLoginController extends Controller
{
    protected int $maxAttempts;
    protected int $decayMinutes;

    public function __construct()
    {
        $this->maxAttempts  = (int) env('LOGIN_MAX_ATTEMPTS', 5);
        $this->decayMinutes = (int) env('LOGIN_DECAY_MINUTES', 15);
    }

    protected function throttleKey(Request $r): string
    {
        return Str::lower((string) $r->input('username')) . '|' . $r->ip();
    }

    public function store(Request $r)
    {
        $key = $this->throttleKey($r);

        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            event(new Lockout($r));
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'username' => "Demasiados intentos. Inténtalo en {$seconds} s.",
            ]);
        }

        $data = $r->validate([
            'username' => ['required','string','min:4','max:50','regex:/^[A-Za-z0-9_.-]+$/'],
            'password' => ['required','string','min:6'],
            'captcha'  => ['required','captcha:flat'],
            '_token'   => ['required'], // CSRF
        ], [
            'username.regex'  => 'Solo letras, números y . _ -',
            'captcha.captcha' => 'El texto de verificación no coincide.',
        ]);

        if (Auth::guard('web')->attempt([
            'username' => $data['username'],
            'password' => $data['password'],
        ], false)) {
            $r->session()->regenerate();
            RateLimiter::clear($key);
            return redirect()->intended('/admin'); // ajusta si tu dashboard es otro
        }

        RateLimiter::hit($key, $this->decayMinutes * 60);

        return back()
            ->withInput($r->only('username'))
            ->withErrors([
                'username' => 'Usuario o contraseña incorrectos.',
                'password' => 'Usuario o contraseña incorrectos.',
            ]);
    }
}
