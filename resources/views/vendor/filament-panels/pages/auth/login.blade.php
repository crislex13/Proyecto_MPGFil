{{-- resources/views/vendor/filament-panels/pages/auth/login.blade.php --}}
<x-filament-panels::page.simple>
    @if (filament()->hasRegistration())
        <x-slot name="subheading">
            {{ __('filament-panels::pages/auth/login.actions.register.before') }}
            {{ $this->registerAction }}
        </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(
    \Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
    scopes: $this->getRenderHookScopes()
) }}


    {{-- BRAND THEME (MaxPower) --}}
    <style>
        :root {
            --brand: #FF6600;
            /* naranja */
            --brand-600: #E65C00;
            /* hover */
            --ink: #4E5054;
            /* gris oscuro */
        }

        /* Botón primario de Filament (Entrar) */
        .fi-btn.fi-color-primary {
            background-color: var(--brand) !important;
            border-color: var(--brand) !important;
            color: #fff !important;
        }

        .fi-btn.fi-color-primary:hover {
            background-color: var(--brand-600) !important;
            border-color: var(--brand-600) !important;
        }
    </style>

    <x-filament-panels::form wire:submit.prevent="authenticate">
        @csrf

        {{-- Banner de error de credenciales --}}
        @if (
                session('auth_error_banner')
                || ($errors->has('data.username') && $errors->first('data.username') === 'Usuario o contraseña incorrectos.')
                || ($errors->has('data.password') && $errors->first('data.password') === 'Usuario o contraseña incorrectos.')
            )
            <div role="alert" aria-live="assertive"
                class="mb-3 rounded-lg border border-danger-500 bg-danger-600/15 px-3 py-2 text-sm text-danger-200">
                <strong>Acceso denegado:</strong> Usuario o contraseña incorrectos.
            </div>
        @endif

        {{-- Banner de lockout (demasiados intentos) --}}
        @if ($errors->has('data.username') && str_contains($errors->first('data.username'), 'Demasiados intentos'))
            <div role="alert" aria-live="assertive"
                class="mb-3 rounded-lg border border-warning-500 bg-warning-600/15 px-3 py-2 text-sm text-warning-200">
                <strong>Bloqueo temporal:</strong> {{ $errors->first('data.username') }}
            </div>
        @endif

        {{-- Banner genérico si hay otros errores --}}
        @if ($errors->any() && !$errors->has('data.username') && !$errors->has('data.password'))
            <div role="alert" aria-live="polite"
                class="mb-3 rounded-lg border border-danger-500 bg-danger-600/15 px-3 py-2 text-sm text-danger-200">
                Revisa los campos marcados e inténtalo nuevamente.
            </div>
        @endif

        <div class="space-y-4">
            {{-- USERNAME --}}
            <div class="space-y-1">
                <label for="username" class="block text-sm font-medium text-[#FF6600]">
                    Nombre de usuario
                </label>
                <input wire:model.defer="data.username" type="text" name="username" id="username"
                    autocomplete="username" required autofocus placeholder="Ej: admin_258877" @error('data.username')
                    aria-invalid="true" aria-describedby="err-username" @enderror
                    class="block w-full rounded-lg border border-[#4E5054] bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400
                           focus:ring-2 focus:ring-[#FF6600] focus:border-[#FF6600] hover:border-[#FF6600]/60 transition" />
                @error('data.username')
                    <p id="err-username" class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- PASSWORD --}}
            <div class="space-y-1">
                <label for="password" class="block text-sm font-medium text-[#FF6600]">
                    Contraseña
                </label>
                <input wire:model.defer="data.password" type="password" name="password" id="password"
                    autocomplete="current-password" required placeholder="••••••••" @error('data.password')
                    aria-invalid="true" aria-describedby="err-password" @enderror
                    class="block w-full rounded-lg border border-[#4E5054] bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400
                           focus:ring-2 focus:ring-[#FF6600] focus:border-[#FF6600] hover:border-[#FF6600]/60 transition" />
                @error('data.password')
                    <p id="err-password" class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- CAPTCHA (mews/captcha) --}}
            <div class="space-y-1 mt-2">
                <label for="captcha" class="block text-sm font-medium text-[#FF6600]">
                    Verificación
                </label>

                <div class="flex items-center gap-3">
                    <img id="captchaImg" src="{{ captcha_src('flat') }}" alt="Imagen de verificación"
                        class="rounded-lg border border-[#4E5054] h-20 bg-white" />
                    <button type="button"
                        onclick="document.getElementById('captchaImg').src='{{ captcha_src('flat') }}&r=' + Date.now()"
                        class="px-3 py-2 rounded-lg border border-[#FF6600] text-[#FF6600]
                               hover:bg-[#FF6600]/10 focus:outline-none focus:ring-2 focus:ring-[#FF6600]"
                        title="Actualizar verificación" aria-label="Actualizar verificación">
                        ↻
                    </button>
                </div>

                <input wire:model.defer="data.captcha" type="text" id="captcha" name="captcha" inputmode="text"
                    autocomplete="off" placeholder="Escribe las letras de la imagen" @error('data.captcha')
                    aria-invalid="true" aria-describedby="err-captcha" @enderror
                    class="block w-full rounded-lg border border-[#4E5054] bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400
                           focus:ring-2 focus:ring-[#FF6600] focus:border-[#FF6600] hover:border-[#FF6600]/60 transition" />
                @error('data.captcha')
                    <p id="err-captcha" class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <x-filament-panels::form.actions :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()" />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(
    \Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
    scopes: $this->getRenderHookScopes()
) }}
</x-filament-panels::page.simple>