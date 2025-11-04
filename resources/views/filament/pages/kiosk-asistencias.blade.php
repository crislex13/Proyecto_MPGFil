<x-filament-panels::page class="!p-0">
    @push('head')
        <style>
            :root {
                --mpg-orange: #FF6600;
                --mpg-dark: #4E5054;
                --mpg-black: #000;
            }

            /* Oculta sidebar y topbar SOLO en esta página */
            .fi-sidebar,
            .fi-topbar,
            .fi-sidebar-header,
            .fi-header,
            [data-sidebar],
            [data-topbar] {
                display: none !important;
            }

            .fi-main,
            .fi-body,
            .fi-content {
                margin: 0 !important;
                padding-left: 0 !important;
                grid-template-columns: 1fr !important;
                width: 100% !important;
            }

            .fi-layout,
            .fi-main>div {
                max-width: 100% !important;
            }

            .fi-main .fi-page {
                padding: 0 !important;
            }

            /* Badge naranja (texto blanco) */
            .badge-mpg {
                display: inline-flex;
                align-items: center;
                gap: .35rem;
                padding: .38rem .75rem;
                border-radius: 9999px;
                font: 700 11px/1 Poppins, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Arial;
                background: var(--mpg-orange);
                color: #fff;
                border: none;
                text-shadow: none;
                -webkit-text-stroke: 0;
            }
        </style>
    @endpush>

    {{-- Refresco en vivo --}}
    <div wire:poll.{{ config('maxpower.kiosk.poll_seconds', 3) }}s
        class="min-h-screen w-full bg-white text-gray-900 dark:bg-gray-950 dark:text-white flex flex-col">

        {{-- Header (tema-aware) --}}
        <header class="px-6 md:px-8 py-4 border-b border-gray-200/70 dark:border-gray-800/70
                       bg-white dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div class="text-xl md:text-2xl font-bold tracking-tight">
                    MAXPOWERGYM • Monitor de Accesos
                </div>
                <div class="text-xs md:text-sm opacity-70">
                    Actualiza cada {{ config('maxpower.kiosk.poll_seconds', 3) }}s
                </div>
            </div>
        </header>

        {{-- Contenido --}}
        <main class="grid grid-cols-12 gap-4 md:gap-6 p-4 md:p-8 flex-1">

            {{-- Columna izquierda: Mensaje principal --}}
            {{-- Columna izquierda: Mensaje principal (bienvenida / denegado) --}}
            <div class="col-span-7 rounded-2xl p-8 flex flex-col justify-center
            bg-white/5 border border-white/10">
                @php $evt = $this->ultimoEvento; @endphp

                @if($evt)
                    @php
                        $esDenegado = $evt->estado === 'acceso_denegado';
                        $nombre = $evt->nombre_completo;
                        $rol = $evt->rol; // Cliente / Personal
                        $hora = optional($evt->hora_entrada)->format('H:i');

                        // Paletas (claro/oscuro) en función del estado
                        $wrap = $esDenegado
                            ? 'bg-rose-500/10 border border-rose-400/30 text-rose-100'
                            : 'bg-emerald-500/10 border border-emerald-400/30 text-emerald-100';

                        $title = $esDenegado ? 'text-rose-400' : 'text-emerald-400';
                        $chip = $esDenegado ? 'bg-rose-500/20 text-rose-100 border border-rose-400/30'
                            : 'bg-emerald-500/20 text-emerald-100 border border-emerald-400/30';
                    @endphp

                    <div class="rounded-2xl p-8 {{ $wrap }}">
                        @if(!$esDenegado)
                            <div class="text-6xl font-extrabold tracking-tight {{ $title }}">
                                ¡BIENVENID{{ $rol === 'Cliente' ? 'O' : 'A' }}!</div>
                            <div class="mt-4 text-4xl font-semibold text-white">{{ $nombre }}</div>
                            <div class="mt-1 text-lg/7 text-white/80">
                                {{ $rol }} • {{ strtoupper($evt->tipo_asistencia) }} • {{ $hora }}
                            </div>

                            {{-- Chips informativos --}}
                            <div class="mt-6 flex flex-wrap gap-2">
                                <span class="px-3 py-1 rounded-full text-sm {{ $chip }}">Ingreso válido</span>
                                <span class="px-3 py-1 rounded-full text-sm {{ $chip }}">Hora {{ $hora }}</span>
                            </div>

                            {{-- Sesiones de HOY para cliente --}}
                            @if($rol === 'Cliente')
                                <div class="mt-8">
                                    <div class="text-2xl font-semibold text-white">Sesiones de hoy</div>
                                    @if($this->sesionesDeHoy->isEmpty())
                                        <div class="mt-2 text-white/70">Sin sesiones registradas para hoy.</div>
                                    @else
                                        <div class="mt-3 space-y-2">
                                            @foreach($this->sesionesDeHoy as $s)
                                                <div class="flex items-center justify-between rounded-xl p-4 bg-white/5">
                                                    <div class="text-xl text-white">
                                                        🕒 {{ \Illuminate\Support\Str::of($s['hora_inicio'])->limit(5, '') }}
                                                        –
                                                        {{ \Illuminate\Support\Str::of($s['hora_fin'])->limit(5, '') }}
                                                    </div>
                                                    <div class="text-xs text-white/60">ID #{{ $s['id'] }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @else
                            <div class="text-6xl font-extrabold tracking-tight {{ $title }}">ACCESO DENEGADO</div>
                            <div class="mt-4 text-4xl font-semibold text-white">{{ $nombre }}</div>
                            <div class="mt-1 text-lg/7 text-white/80">
                                {{ $rol }} • {{ strtoupper($evt->tipo_asistencia) }} • {{ $hora }}
                            </div>

                            <div class="mt-6 rounded-2xl p-6 bg-rose-500/15 border border-rose-400/30 text-rose-100 text-2xl">
                                {{ $evt->observacion ?? 'Fuera de horario o sin condiciones para ingresar.' }}
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="px-3 py-1 rounded-full text-sm {{ $chip }}">Revisar condiciones</span>
                                <span class="px-3 py-1 rounded-full text-sm {{ $chip }}">Hora {{ $hora }}</span>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-5xl font-bold opacity-60">Listo para marcar…</div>
                    <div class="mt-2 text-xl opacity-50">Cuando alguien marque, aquí verás la bienvenida o el motivo del
                        rechazo.</div>
                @endif
            </div>

            {{-- Columna derecha: Alertas de salida --}}
            <aside class="col-span-12 lg:col-span-5">
                <div class="rounded-2xl border shadow-sm
                            bg-white border-gray-200 text-gray-900
                            dark:bg-gray-900 dark:border-gray-800 dark:text-white p-6 md:p-8">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg md:text-xl font-semibold">Alertas de salida</h3>
                        <span class="text-xs md:text-sm opacity-70">
                            Umbral: {{ config('maxpower.kiosk.warn_minutes', 5) }} min
                        </span>
                    </div>

                    @if($this->advertencias->isEmpty())
                        <p class="mt-5 text-sm md:text-base opacity-70">Sin alertas por ahora.</p>
                    @else
                        <div class="mt-5 space-y-4">
                            @foreach($this->advertencias as $a)
                                <div class="rounded-2xl p-5
                                                    bg-amber-500/10 border border-amber-400/30">
                                    <div class="text-lg md:text-xl font-semibold">
                                        ⏳ {{ $a->nombre_completo }}
                                    </div>
                                    <div class="mt-1 text-xs md:text-sm opacity-80">
                                        {{ ucfirst($a->rol) }} • {{ strtoupper($a->tipo_asistencia) }}
                                        • Entró: {{ optional($a->hora_entrada)->format('H:i') }}
                                    </div>
                                    <div class="mt-3 text-2xl md:text-3xl font-extrabold text-amber-300">
                                        Le quedan {{ $a->min_restantes }} min
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-6 text-[11px] md:text-xs opacity-60">
                        Las asistencias se cierran solas si no marcan salida (cron de autocierre activo).
                    </div>
                </div>
            </aside>
        </main>

        {{-- Footer --}}
        <footer class="px-6 md:px-8 py-3 border-t border-gray-200/70 dark:border-gray-800/70
                        bg-white/60 dark:bg-gray-900/60 backdrop-blur">
            <div class="text-xs md:text-sm opacity-70">
                {{ now()->format('d/m/Y H:i:s') }} • MAXPOWERGYM
            </div>
        </footer>
    </div>
</x-filament-panels::page>