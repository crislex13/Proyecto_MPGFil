<x-filament::page>
    {{-- Sección de estadísticas generales --}}
    <x-filament::section heading="📈 Resumen Estadístico">
        @livewire('resumen-estadistico')
        <!--@livewire(App\Filament\Widgets\InscripcionesPorDia::class)-->
    </x-filament::section>

    {{-- Sección de rendimiento financiero --}}
    <x-filament::section heading="💰 Finanzas del Día">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @livewire('flujo-caja-dia-widget')
            <!--@livewire(App\Filament\Widgets\FlujoCajaSemana::class)-->
        </div>
    </x-filament::section>

    {{-- Sección de Top del mes --}}
    <x-filament::section heading="🏆 Lo más destacado del mes">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @livewire('instructor-top-widget')
            @livewire('producto-top-widget')
        </div>
    </x-filament::section>
</x-filament::page>