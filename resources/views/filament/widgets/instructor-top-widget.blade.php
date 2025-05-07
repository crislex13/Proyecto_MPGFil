@php
    $imageSize = 'w-20 h-20'; // Ajusta aquí el tamaño
@endphp

<x-filament::card>
    @if ($instructor)
        <div class="flex flex-col items-center text-center space-y-2">
            <img
                src="{{ asset('storage/' . $instructor->foto) }}"
                alt="Foto del Instructor"
                class="{{ $imageSize }} rounded-full object-cover shadow-md mb-2"
            />

            <h2 class="text-base font-bold text-gray-100">
                {{ $instructor->nombre }} {{ $instructor->apellido_paterno }} {{ $instructor->apellido_materno }}
            </h2>
            <p class="text-xs text-gray-400">Instructor más cotizado del mes</p>

            <div class="text-sm text-gray-300 mt-1 space-y-1">
                <p>📅 <strong>{{ $totalSesiones }}</strong> sesiones</p>
                <p>💸 <strong>{{ number_format($totalGanancias, 2) }} Bs</strong></p>
            </div>
        </div>
    @else
        <p class="text-center text-gray-500">No hay sesiones registradas este mes.</p>
    @endif
</x-filament::card>