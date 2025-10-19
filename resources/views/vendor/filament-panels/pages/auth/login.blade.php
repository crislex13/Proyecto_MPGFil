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

    <x-filament-panels::form wire:submit.prevent="authenticate">
        @csrf
        <div class="space-y-4">
            <div class="space-y-1">
                <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Nombre de usuario
                </label>
                <input wire:model.defer="data.username" type="text" name="username" id="username"
                    autocomplete="username" required autofocus placeholder="Ej: admin_258877"
                    class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
            </div>

            <div class="space-y-1">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Contraseña
                </label>
                <input wire:model.defer="data.password" type="password" name="password" id="password"
                    autocomplete="current-password" required placeholder="••••••••"
                    class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />
            </div>
            {{-- CAPTCHA de imagen (mews/captcha) --}}
            <div class="space-y-1 mt-2">
                <label for="captcha" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Verificación
                </label>

                <div class="flex items-center gap-3">
                    <img id="captchaImg" src="{{ captcha_src('flat') }}" {{-- usa el preset "flat" --}}
                        alt="Imagen de verificación"
                        class="rounded-lg border border-gray-300 dark:border-gray-600 h-12 bg-white" />

                    <button type="button"
                        onclick="document.getElementById('captchaImg').src='{{ captcha_src('flat') }}&r=' + Date.now()"
                        class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800"
                        title="Actualizar verificación" aria-label="Actualizar verificación">
                        ↻
                    </button>
                </div>

                <input wire:model.defer="data.captcha" type="text" id="captcha" name="captcha" autocomplete="off"
                    placeholder="Escribe las letras de la imagen"
                    class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500" />

                @error('data.captcha')
                    <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
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