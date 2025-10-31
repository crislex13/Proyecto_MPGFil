<x-filament-panels::page class="!p-0">
    @push('head')
    <style>
      /* Oculta sidebar y topbar SOLO en esta página */
      .fi-sidebar,
      .fi-topbar,
      .fi-sidebar-header,
      .fi-header,
      [data-sidebar],
      [data-topbar] {
        display: none !important;
      }
      /* Expande el contenido a todo el ancho al ocultar el sidebar */
      .fi-main, .fi-body, .fi-content {
        margin: 0 !important;
        padding-left: 0 !important;
        grid-template-columns: 1fr !important;
        width: 100% !important;
      }
      /* Evita reservas de espacio del layout */
      .fi-layout, .fi-main>div {
        max-width: 100% !important;
      }
      /* Quita posibles rellenos */
      .fi-main .fi-page {
        padding: 0 !important;
      }
    </style>
    @endpush

    <div class="min-h-screen w-full bg-black text-white flex flex-col">

        {{-- Header simple --}}
        <div class="px-8 py-4 border-b border-white/10 flex items-center justify-between">
            <div class="text-2xl font-semibold tracking-wide">MAXPOWERGYM • Monitor de Accesos</div>
            <div class="text-sm opacity-70">Actualiza cada {{ config('maxpower.kiosk.poll_seconds', 3) }}s</div>
        </div>

        <div class="grid grid-cols-12 gap-6 p-8 flex-1">
            {{-- Columna izquierda: Mensaje principal (bienvenida / denegado) --}}
            <div class="col-span-7 bg-white/5 rounded-2xl p-8 flex flex-col justify-center">
                @php $evt = $this->ultimoEvento; @endphp

                @if($evt)
                    @php
                        $esDenegado = $evt->estado === 'acceso_denegado';
                        $nombre = $evt->nombre_completo;
                        $rol    = $evt->rol; // Cliente / Personal
                        $hora   = optional($evt->hora_entrada)->format('H:i');
                    @endphp

                    @if(!$esDenegado)
                        <div class="text-6xl font-bold text-emerald-400">¡BIENVENID{{ $rol === 'Cliente' ? 'O' : 'A' }}!</div>
                        <div class="mt-4 text-4xl font-semibold">{{ $nombre }}</div>
                        <div class="mt-1 text-xl opacity-70">{{ $rol }} • {{ strtoupper($evt->tipo_asistencia) }} • {{ $hora }}</div>

                        @if($rol === 'Cliente')
                            {{-- Sesiones de HOY --}}
                            <div class="mt-8">
                                <div class="text-2xl font-semibold mb-3">Sesiones de hoy</div>
                                @if($this->sesionesDeHoy->isEmpty())
                                    <div class="text-lg opacity-70">Sin sesiones registradas para hoy.</div>
                                @else
                                    <div class="space-y-2">
                                        @foreach($this->sesionesDeHoy as $s)
                                            <div class="flex items-center justify-between bg-white/5 rounded-xl p-4">
                                                <div class="text-xl">🕒 {{ \Illuminate\Support\Str::of($s['hora_inicio'])->limit(5,'') }}–{{ \Illuminate\Support\Str::of($s['hora_fin'])->limit(5,'') }}</div>
                                                <div class="text-sm opacity-70">ID #{{ $s['id'] }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    @else
                        <div class="text-6xl font-bold text-rose-400">ACCESO DENEGADO</div>
                        <div class="mt-4 text-4xl font-semibold">{{ $nombre }}</div>
                        <div class="mt-1 text-xl opacity-70">{{ $rol }} • {{ strtoupper($evt->tipo_asistencia) }} • {{ $hora }}</div>
                        <div class="mt-6 bg-rose-500/10 border border-rose-500/30 rounded-2xl p-6 text-2xl leading-snug">
                            {{ $evt->observacion ?? 'Fuera de horario o sin condiciones para ingresar.' }}
                        </div>
                    @endif
                @else
                    <div class="text-5xl font-bold opacity-60">Listo para marcar…</div>
                    <div class="mt-2 text-xl opacity-50">Cuando alguien marque, aquí verás la bienvenida o el motivo del rechazo.</div>
                @endif
            </div>

            {{-- Columna derecha: Alertas de “faltan 5 min” --}}
            <div class="col-span-5 bg-white/5 rounded-2xl p-8">
                <div class="flex items-center justify-between">
                    <div class="text-3xl font-semibold">Alertas de salida</div>
                    <div class="text-sm opacity-70">Umbral: {{ config('maxpower.kiosk.warn_minutes', 5) }} min</div>
                </div>

                @if($this->advertencias->isEmpty())
                    <div class="mt-6 text-lg opacity-60">Sin alertas por ahora.</div>
                @else
                    <div class="mt-6 space-y-4">
                        @foreach($this->advertencias as $a)
                            <div class="rounded-2xl p-5 bg-amber-500/10 border border-amber-400/30">
                                <div class="text-2xl font-semibold">⏳ {{ $a->nombre_completo }}</div>
                                <div class="mt-1 text-sm opacity-70">
                                    {{ ucfirst($a->rol) }} • {{ strtoupper($a->tipo_asistencia) }} • Entró: {{ optional($a->hora_entrada)->format('H:i') }}
                                </div>
                                <div class="mt-3 text-3xl font-bold text-amber-300">
                                    Le quedan {{ $a->min_restantes }} min
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-8 text-xs opacity-50">
                    Las asistencias se cierran solas si no marcan salida (cron de autocierre activo).
                </div>
            </div>
        </div>

        {{-- Footer minimal --}}
        <div class="px-8 py-3 border-t border-white/10 text-sm opacity-50">
            {{ now()->format('d/m/Y H:i:s') }} • MAXPOWERGYM
        </div>
    </div>
</x-filament-panels::page>
