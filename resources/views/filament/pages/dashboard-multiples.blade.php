<x-filament::page>
    <div class="space-y-6">

        {{-- Banner motivacional --}}
        <div
            class="rounded-xl bg-[#FF6600] px-6 py-4 shadow-lg text-white flex flex-col md:flex-row items-center justify-between">
            <div class="text-xl font-bold uppercase tracking-wide">
                ¡Acepta el desafío, rompe los límites!
            </div>
            <div class="text-sm mt-2 md:mt-0 italic text-white/80">
                MAXPOWERGYM — Dashboard combinado
            </div>
        </div>

        {{-- Saludo --}}
        <h2 class="text-2xl font-bold text-[#4E5054]">Hola {{ auth()->user()->name }} 👋</h2>

        {{-- Cliente: Plan Actual --}}
        @if ($cliente)
            <div class="p-4 border border-[#FF6600] rounded-lg shadow-md bg-white/5">
                <h3 class="text-lg font-semibold text-[#FF6600]">📋 Plan Actual</h3>
                @if ($cliente->planesCliente->last())
                    <ul class="mt-2 list-disc ml-6 text-white">
                        <li><strong>Plan:</strong> {{ $cliente->planesCliente->last()->plan->nombre }}</li>
                        <li><strong>Inicio:</strong> {{ $cliente->planesCliente->last()->fecha_inicio->format('d/m/Y') }}</li>
                        <li><strong>Fin:</strong> {{ $cliente->planesCliente->last()->fecha_final->format('d/m/Y') }}</li>
                        <li><strong>Días restantes:</strong>
                            {{ now()->diffInDays($cliente->planesCliente->last()->fecha_final, false) }} días</li>
                    </ul>
                @else
                    <p class="text-white mt-2">No tienes ningún plan activo.</p>
                @endif
            </div>

            {{-- Cliente: Asistencias recientes --}}
            <div class="p-4 border border-[#FF6600] rounded-lg shadow-md bg-white/5">
                <h3 class="text-lg font-semibold text-[#FF6600]">🕒 Asistencias recientes</h3>
                @if ($cliente->asistencias->count())
                    <ul class="mt-2 list-disc ml-6 text-white">
                        @foreach($cliente->asistencias->take(5) as $a)
                            <li>{{ $a->fecha }} - {{ $a->hora_entrada }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-white mt-2">No se encontraron asistencias recientes.</p>
                @endif
            </div>

            {{-- Cliente: Sesiones Adicionales --}}
            <div class="p-4 border border-[#FF6600] rounded-lg shadow-md bg-white/5">
                <h3 class="text-lg font-semibold text-[#FF6600]">🏋️‍♂️ Sesiones Adicionales</h3>
                @if ($cliente->sesionesAdicionales->count())
                    <ul class="mt-2 list-disc ml-6 text-white">
                        @foreach($cliente->sesionesAdicionales->take(5) as $s)
                            <li>{{ $s->fecha }} - {{ $s->tipo_sesion }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-white mt-2">No tienes sesiones adicionales registradas.</p>
                @endif
            </div>
        @endif

        {{-- Instructor: Turnos --}}
        @if ($instructor)
            <div class="p-4 border border-[#4E5054] rounded-lg shadow-md bg-white/5">
                <h3 class="text-lg font-semibold text-[#4E5054]">📅 Turnos de la Semana</h3>
                @if ($instructor->turnos->count())
                    <ul class="mt-2 list-disc ml-6 text-white">
                        @foreach($instructor->turnos as $turno)
                            <li>{{ $turno->dia }} - {{ $turno->nombre }} ({{ $turno->hora_inicio }} - {{ $turno->hora_fin }})</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-white mt-2">No tienes turnos asignados esta semana.</p>
                @endif
            </div>

            {{-- Instructor: Asistencias --}}
            <div class="p-4 border border-[#4E5054] rounded-lg shadow-md bg-white/5">
                <h3 class="text-lg font-semibold text-[#4E5054]">🗓️ Asistencias Recientes</h3>
                @if ($instructor->asistencias->count())
                    <ul class="mt-2 list-disc ml-6 text-white">
                        @foreach($instructor->asistencias->take(5) as $asistencia)
                            <li>{{ $asistencia->fecha }} - {{ $asistencia->hora_entrada }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-white mt-2">No se encontraron asistencias recientes.</p>
                @endif
            </div>
        @endif

    </div>
</x-filament::page>