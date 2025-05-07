@php
    $imageSize = 'w-20 h-20'; // Cambia aquí para ajustar el tamaño
@endphp

<x-filament::card>
    @if ($producto)
        <div class="flex flex-col items-center text-center space-y-2">
            @if ($producto->imagen)
                <img
                    src="{{ asset('storage/' . $producto->imagen) }}"
                    alt="Imagen del producto"
                    class="{{ $imageSize }} rounded-xl object-cover shadow-md mb-2"
                />
            @endif

            <h2 class="text-base font-bold text-gray-100">
                {{ $producto->nombre }}
            </h2>
            <p class="text-xs text-gray-400">Producto más vendido del mes</p>

            <div class="text-sm text-gray-300 mt-1 space-y-1">
                <p>🛒 Vendidas: <strong>{{ $totalVendidas }}</strong></p>
                <p>💰 Total: <strong>{{ number_format($totalGenerado, 2) }} Bs</strong></p>
            </div>
        </div>
    @else
        <p class="text-center text-gray-500">No hay ventas registradas este mes.</p>
    @endif
</x-filament::card>