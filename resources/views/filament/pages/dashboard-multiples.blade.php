<x-filament::page>
    <style>
        :root { --mpg-orange:#FF6600; --mpg-dark:#4E5054; --mpg-black:#000; }

        @font-face { font-family:'Cornero';  src:url('/fonts/Cornero-Regular.woff2')  format('woff2'); font-display:swap; }
        @font-face { font-family:'Geometos'; src:url('/fonts/Geometos-Regular.woff2') format('woff2'); font-display:swap; }
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

        .badge-mpg{
            display:inline-flex;align-items:center;gap:.35rem;
            padding:.38rem .75rem;border-radius:9999px;
            font:700 11px/1 Poppins,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Arial;
            background:var(--mpg-orange);color:#fff;border:none;text-shadow:none;-webkit-text-stroke:0;
        }
    </style>

    @php
        // --------- CLIENTE ----------
        $planActual = optional($cliente?->planesCliente)->last();
        $diasRestantes = $planActual
            ? now()->startOfDay()->diffInDays($planActual->fecha_final->startOfDay(), false)
            : null;

        // Asistencias válidas del cliente
        $validasCliente = collect();
        if ($cliente) {
            $validasCliente = $cliente->asistencias
                ->whereIn('estado', ['puntual','atrasado','permiso'])
                ->sortByDesc(fn($a) => \Carbon\Carbon::parse($a->fecha)->format('Y-m-d').' '.\Carbon\Carbon::parse($a->hora_entrada)->format('H:i:s'))
                ->values();
        }
        $validasClienteCount = $validasCliente->count();
        $validasClienteTop5  = $validasCliente->take(5);

        // --------- INSTRUCTOR ----------
        // Turnos ya vienen en $instructor->turnos
        // Asistencias recientes del instructor (sin filtrar duro por tipo/estado para no ocultar datos)
        $asisInstructorTop5 = collect();
        if ($instructor) {
            $asisInstructorTop5 = $instructor->asistencias
                ->sortByDesc(fn($a) => \Carbon\Carbon::parse($a->fecha)->format('Y-m-d').' '.\Carbon\Carbon::parse($a->hora_entrada)->format('H:i:s'))
                ->take(5)
                ->values();
        }
    @endphp

    <div class="space-y-6 font-[Poppins]">

        {{-- HERO unificado (tema-aware) --}}
        <section class="rounded-2xl border p-6 shadow-sm
                        bg-white border-gray-200 text-gray-900
                        dark:bg-gray-900 dark:border-gray-800 dark:text-white">
            <div class="text-center space-y-2">
                <h2 class="text-2xl md:text-3xl font-bold tracking-tight"
                    style="font-family:'Cornero','Poppins',sans-serif">
                    ¡Hola {{ auth()->user()->name }}!
                </h2>
                <p class="text-sm md:text-base/6 text-gray-700 dark:text-gray-300">
                    ¡Acepta el desafío, rompe los límites! 🥇
                </p>

                <div class="pt-1 flex items-center justify-center gap-2">
                    @if($cliente)   <span class="badge-mpg">Cliente</span>   @endif
                    @if($instructor)<span class="badge-mpg">Instructor</span>@endif
                </div>
            </div>

            <div class="mt-4 mx-auto h-[3px] w-32 rounded-full
                        bg-gradient-to-r from-[color:var(--mpg-orange)] to-[color:var(--mpg-black)]">
            </div>
        </section>

        {{-- ======= BLOQUE CLIENTE ======= --}}
        @if($cliente)
            {{-- KPIs Cliente --}}
            <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Plan --}}
                <div class="rounded-2xl border p-6 shadow-sm text-center
                            bg-white border-gray-200 text-gray-900
                            dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                    <div class="flex items-center justify-center gap-2">
                        <h3 class="text-[12px] font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Plan</h3>
                        <span class="badge-mpg">Actual</span>
                    </div>
                    <div class="mt-3 text-3xl font-bold tracking-tight">
                        {{ $planActual?->plan?->nombre ?? 'Sin plan' }}
                    </div>
                    <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                        @if($planActual)
                            {{ $planActual->fecha_inicio->format('d/m/Y') }} — {{ $planActual->fecha_final->format('d/m/Y') }}
                        @else
                            Adquiere un plan para comenzar.
                        @endif
                    </div>
                    @if(!is_null($diasRestantes))
                        <div class="mt-4">
                            <div class="mx-auto h-[3px] w-32 rounded-full
                                        bg-gradient-to-r from-[color:var(--mpg-orange)] to-[color:var(--mpg-black)]"></div>
                            <div class="mt-3 text-sm">
                                <span class="font-semibold text-gray-800 dark:text-gray-200">
                                    Días restantes: {{ $diasRestantes }} {{ $diasRestantes===1?'día':'días' }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Asistencias válidas --}}
                <div class="rounded-2xl border p-6 shadow-sm text-center
                            bg-white border-gray-200 text-gray-900
                            dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                    <div class="flex items-center justify-center gap-2">
                        <h3 class="text-[12px] font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Asistencias</h3>
                        <span class="badge-mpg">Válidas</span>
                    </div>
                    <div class="mt-3 text-4xl font-extrabold tracking-tight">
                        {{ $validasClienteCount }}
                    </div>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">Entradas reales registradas.</p>
                </div>

                {{-- Sesiones adicionales (conteo) --}}
                <div class="rounded-2xl border p-6 shadow-sm text-center
                            bg-white border-gray-200 text-gray-900
                            dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                    <div class="flex items-center justify-center gap-2">
                        <h3 class="text-[12px] font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                            Sesiones Adicionales</h3>
                        <span class="badge-mpg">Total</span>
                    </div>
                    <div class="mt-3 text-4xl font-extrabold tracking-tight">
                        {{ $cliente->sesionesAdicionales->count() }}
                    </div>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">Registros vinculados a tu cuenta.</p>
                </div>
            </section>

            {{-- Detalle Cliente --}}
            <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Plan detalle --}}
                <div class="rounded-2xl border p-6 shadow-sm text-center
                            bg-white border-gray-200 text-gray-900
                            dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                    <h3 class="text-lg font-semibold" style="font-family:'Geometos','Poppins',sans-serif">
                        📋 Plan Actual
                    </h3>
                    <div class="mt-3 text-sm text-gray-800 dark:text-gray-300 space-y-1">
                        @if ($planActual)
                            <p><strong>Plan:</strong> {{ $planActual->plan->nombre }}</p>
                            <p><strong>Inicio:</strong> {{ $planActual->fecha_inicio->format('d/m/Y') }}</p>
                            <p><strong>Fin:</strong> {{ $planActual->fecha_final->format('d/m/Y') }}</p>
                            <p class="mt-2"><span class="badge-mpg">Días restantes: {{ $diasRestantes }}</span></p>
                        @else
                            <p>No tienes ningún plan activo.</p>
                        @endif
                    </div>
                </div>

                {{-- Asistencias recientes (solo válidas) --}}
                <div class="rounded-2xl border p-6 shadow-sm text-center
                            bg-white border-gray-200 text-gray-900
                            dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                    <h3 class="text-lg font-semibold" style="font-family:'Geometos','Poppins',sans-serif">
                        🕒 Asistencias recientes
                    </h3>
                    <div class="mt-3 text-sm text-gray-800 dark:text-gray-300">
                        @if ($validasClienteTop5->count())
                            <ul class="inline-block text-left space-y-1">
                                @foreach($validasClienteTop5 as $a)
                                    <li>
                                        <span class="font-semibold text-gray-900 dark:text-white">
                                            {{ \Carbon\Carbon::parse($a->fecha)->format('d/m/Y') }}
                                        </span>
                                        — {{ \Carbon\Carbon::parse($a->hora_entrada)->format('H:i') }}
                                        <span class="badge-mpg ml-2">{{ strtoupper($a->estado) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p>No se encontraron asistencias válidas recientes.</p>
                        @endif
                    </div>
                </div>

                {{-- Sesiones adicionales (lista) --}}
                <div class="rounded-2xl border p-6 shadow-sm text-center
                            bg-white border-gray-200 text-gray-900
                            dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                    <h3 class="text-lg font-semibold" style="font-family:'Geometos','Poppins',sans-serif">
                        🏋️‍♂️ Sesiones Adicionales
                    </h3>
                    <div class="mt-3 text-sm text-gray-800 dark:text-gray-300">
                        @if ($cliente->sesionesAdicionales->count())
                            <ul class="inline-block text-left space-y-1">
                                @foreach($cliente->sesionesAdicionales->take(5) as $s)
                                    <li>
                                        <span class="font-semibold text-gray-900 dark:text-white">
                                            {{ \Carbon\Carbon::parse($s->fecha)->format('d/m/Y') }}
                                        </span>
                                        — {{ $s->tipo_sesion }}
                                        @if($s->hora_inicio && $s->hora_fin)
                                            <span class="ml-1 text-gray-600 dark:text-gray-400">
                                                {{ \Carbon\Carbon::parse($s->hora_inicio)->format('H:i') }}–{{ \Carbon\Carbon::parse($s->hora_fin)->format('H:i') }}
                                            </span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p>No tienes sesiones adicionales registradas.</p>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        {{-- ======= BLOQUE INSTRUCTOR ======= --}}
        @if($instructor)
            {{-- KPIs Instructor --}}
            <section class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Turnos de la semana --}}
                <div class="rounded-2xl border p-6 shadow-sm
                            bg-white border-gray-200 text-gray-900
                            dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold" style="font-family:'Geometos','Poppins',sans-serif">
                            📅 Turnos de la semana
                        </h3>
                        <span class="badge-mpg">Instructor</span>
                    </div>
                    <div class="mt-3 text-sm text-gray-800 dark:text-gray-300">
                        @if ($instructor->turnos->count())
                            <ul class="list-disc ml-6 space-y-1">
                                @foreach($instructor->turnos as $turno)
                                    <li>
                                        <span class="font-semibold text-gray-900 dark:text-white">
                                            {{ $turno->dia }}
                                        </span> — {{ $turno->nombre }}
                                        ({{ $turno->hora_inicio }} - {{ $turno->hora_fin }})
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p>No tienes turnos asignados esta semana.</p>
                        @endif
                    </div>
                </div>

                {{-- Asistencias del instructor --}}
                <div class="rounded-2xl border p-6 shadow-sm
                            bg-white border-gray-200 text-gray-900
                            dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                    <h3 class="text-lg font-semibold" style="font-family:'Geometos','Poppins',sans-serif">
                        🗓️ Asistencias recientes
                    </h3>
                    <div class="mt-3 text-sm text-gray-800 dark:text-gray-300">
                        @if ($asisInstructorTop5->count())
                            <ul class="list-disc ml-6 space-y-1">
                                @foreach($asisInstructorTop5 as $a)
                                    <li>
                                        <span class="font-semibold text-gray-900 dark:text-white">
                                            {{ \Carbon\Carbon::parse($a->fecha)->format('d/m/Y') }}
                                        </span>
                                        — {{ \Carbon\Carbon::parse($a->hora_entrada)->format('H:i') }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p>No se encontraron asistencias recientes.</p>
                        @endif
                    </div>
                </div>
            </section>
        @endif

    </div>
</x-filament::page>
