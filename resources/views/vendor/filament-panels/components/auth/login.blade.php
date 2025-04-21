<x-filament::layouts.app>
    <div class="flex flex-col items-center justify-center min-h-screen bg-black text-white">
        {{-- LOGO --}}
        <img src="{{ asset('images/LogosMPG/Recurso 6.png') }}" alt="Logo MaxPowerGym" class="w-48 mb-6">

        <h1 class="text-2xl font-bold tracking-widest">MAX POWER GYM</h1>
        <p class="mb-6 text-lg">Entre a su cuenta</p>

        {{-- FORMULARIO --}}
        <form wire:submit.prevent="authenticate" class="w-full max-w-sm space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit" class="w-full bg-[#FF6600] hover:bg-[#e65c00]">
                Entrar
            </x-filament::button>
        </form>
    </div>
</x-filament::layouts.app>