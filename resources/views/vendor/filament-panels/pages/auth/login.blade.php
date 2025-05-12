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
        </div>

        <x-filament-panels::form.actions :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()" />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(
    \Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
    scopes: $this->getRenderHookScopes()
) }}
</x-filament-panels::page.simple>