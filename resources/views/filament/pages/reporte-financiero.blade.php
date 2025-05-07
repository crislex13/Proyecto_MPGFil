<x-filament::page>
    <div class="space-y-4">
        {{ $this->form }}

        <x-filament::button
            wire:click="generarPDF"
            color="success"
            size="md"
            icon="heroicon-o-arrow-down-tray"
            class="mt-4"
        >
            Descargar PDF
        </x-filament::button>
    </div>
</x-filament::page>
