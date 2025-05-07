<x-filament::page>
    <div class="space-y-4">

        <h2 class="text-xl font-bold text-primary">¡Bienvenido {{ auth()->user()->name }}!</h2>

        <div class="p-4 border rounded-lg shadow">
            <h3 class="text-lg font-semibold text-orange-600">📅 Turnos de la Semana</h3>
            @if ($instructor->turnos->count())
                <ul class="list-disc ml-5">
                    @foreach($instructor->turnos as $turno)
                        <li>{{ $turno->dia }} - {{ $turno->nombre }} ({{ $turno->hora_inicio }} - {{ $turno->hora_fin }})</li>
                    @endforeach
                </ul>
            @else
                <p>No tienes turnos asignados esta semana.</p>
            @endif
        </div>

        <div class="p-4 border rounded-lg shadow">
            <h3 class="text-lg font-semibold text-orange-600">🗓️ Asistencias Recientes</h3>
            @if ($instructor->asistencias->count())
                <ul class="list-disc ml-5">
                    @foreach($instructor->asistencias as $asistencia)
                        <li>{{ $asistencia->fecha }} - {{ $asistencia->hora_entrada }}</li>
                    @endforeach
                </ul>
            @else
                <p>No se encontraron asistencias recientes.</p>
            @endif
        </div>

    </div>
</x-filament::page>