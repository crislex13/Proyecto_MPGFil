<x-filament::page>
    <div class="space-y-4">

        <h2 class="text-xl font-bold text-primary">¡Hola {{ auth()->user()->name }}!</h2>

        <div class="p-4 border rounded-lg shadow">
            <h3 class="text-lg font-semibold text-orange-600">📋 Plan Actual</h3>
            @if ($cliente->planesCliente->last())
                <p><strong>Plan:</strong> {{ $cliente->planesCliente->last()->plan->nombre }}</p>
                <p><strong>Inicio:</strong> {{ $cliente->planesCliente->last()->fecha_inicio->format('d/m/Y') }}</p>
                <p><strong>Fin:</strong> {{ $cliente->planesCliente->last()->fecha_final->format('d/m/Y') }}</p>
                <p><strong>Días restantes:</strong>
                    {{ now()->diffInDays($cliente->planesCliente->last()->fecha_final, false) }} días</p>
            @else
                <p>No tienes ningún plan activo.</p>
            @endif
        </div>

        <div class="p-4 border rounded-lg shadow">
            <h3 class="text-lg font-semibold text-orange-600">🕒 Asistencias recientes</h3>
            @if ($cliente->asistencias->count())
                <ul class="list-disc ml-5">
                    @foreach($cliente->asistencias->take(5) as $a)
                        <li>{{ $a->fecha }} - {{ $a->hora_entrada }}</li>
                    @endforeach
                </ul>
            @else
                <p>No se encontraron asistencias recientes.</p>
            @endif
        </div>

        <div class="p-4 border rounded-lg shadow">
            <h3 class="text-lg font-semibold text-orange-600">🏋️‍♂️ Sesiones Adicionales</h3>
            @if ($cliente->sesionesAdicionales->count())
                <ul class="list-disc ml-5">
                    @foreach($cliente->sesionesAdicionales->take(5) as $s)
                        <li>{{ $s->fecha }} - {{ $s->tipo_sesion }}</li>
                    @endforeach
                </ul>
            @else
                <p>No tienes sesiones adicionales registradas.</p>
            @endif
        </div>

    </div>
</x-filament::page>