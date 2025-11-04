<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => ['class' => '!p-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => '!p-0']); ?>
    <?php $__env->startPush('head'); ?>
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
    <?php $__env->stopPush(); ?>>

    
    <div wire:poll.<?php echo e(config('maxpower.kiosk.poll_seconds', 3)); ?>s
        class="min-h-screen w-full bg-white text-gray-900 dark:bg-gray-950 dark:text-white flex flex-col">

        
        <header class="px-6 md:px-8 py-4 border-b border-gray-200/70 dark:border-gray-800/70
                       bg-white dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div class="text-xl md:text-2xl font-bold tracking-tight">
                    MAXPOWERGYM • Monitor de Accesos
                </div>
                <div class="text-xs md:text-sm opacity-70">
                    Actualiza cada <?php echo e(config('maxpower.kiosk.poll_seconds', 3)); ?>s
                </div>
            </div>
        </header>

        
        <main class="grid grid-cols-12 gap-4 md:gap-6 p-4 md:p-8 flex-1">

            
            
            <div class="col-span-7 rounded-2xl p-8 flex flex-col justify-center
            bg-white/5 border border-white/10">
                <?php $evt = $this->ultimoEvento; ?>

                <!--[if BLOCK]><![endif]--><?php if($evt): ?>
                    <?php
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
                    ?>

                    <div class="rounded-2xl p-8 <?php echo e($wrap); ?>">
                        <!--[if BLOCK]><![endif]--><?php if(!$esDenegado): ?>
                            <div class="text-6xl font-extrabold tracking-tight <?php echo e($title); ?>">
                                ¡BIENVENID<?php echo e($rol === 'Cliente' ? 'O' : 'A'); ?>!</div>
                            <div class="mt-4 text-4xl font-semibold text-white"><?php echo e($nombre); ?></div>
                            <div class="mt-1 text-lg/7 text-white/80">
                                <?php echo e($rol); ?> • <?php echo e(strtoupper($evt->tipo_asistencia)); ?> • <?php echo e($hora); ?>

                            </div>

                            
                            <div class="mt-6 flex flex-wrap gap-2">
                                <span class="px-3 py-1 rounded-full text-sm <?php echo e($chip); ?>">Ingreso válido</span>
                                <span class="px-3 py-1 rounded-full text-sm <?php echo e($chip); ?>">Hora <?php echo e($hora); ?></span>
                            </div>

                            
                            <!--[if BLOCK]><![endif]--><?php if($rol === 'Cliente'): ?>
                                <div class="mt-8">
                                    <div class="text-2xl font-semibold text-white">Sesiones de hoy</div>
                                    <!--[if BLOCK]><![endif]--><?php if($this->sesionesDeHoy->isEmpty()): ?>
                                        <div class="mt-2 text-white/70">Sin sesiones registradas para hoy.</div>
                                    <?php else: ?>
                                        <div class="mt-3 space-y-2">
                                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->sesionesDeHoy; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="flex items-center justify-between rounded-xl p-4 bg-white/5">
                                                    <div class="text-xl text-white">
                                                        🕒 <?php echo e(\Illuminate\Support\Str::of($s['hora_inicio'])->limit(5, '')); ?>

                                                        –
                                                        <?php echo e(\Illuminate\Support\Str::of($s['hora_fin'])->limit(5, '')); ?>

                                                    </div>
                                                    <div class="text-xs text-white/60">ID #<?php echo e($s['id']); ?></div>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <?php else: ?>
                            <div class="text-6xl font-extrabold tracking-tight <?php echo e($title); ?>">ACCESO DENEGADO</div>
                            <div class="mt-4 text-4xl font-semibold text-white"><?php echo e($nombre); ?></div>
                            <div class="mt-1 text-lg/7 text-white/80">
                                <?php echo e($rol); ?> • <?php echo e(strtoupper($evt->tipo_asistencia)); ?> • <?php echo e($hora); ?>

                            </div>

                            <div class="mt-6 rounded-2xl p-6 bg-rose-500/15 border border-rose-400/30 text-rose-100 text-2xl">
                                <?php echo e($evt->observacion ?? 'Fuera de horario o sin condiciones para ingresar.'); ?>

                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="px-3 py-1 rounded-full text-sm <?php echo e($chip); ?>">Revisar condiciones</span>
                                <span class="px-3 py-1 rounded-full text-sm <?php echo e($chip); ?>">Hora <?php echo e($hora); ?></span>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                <?php else: ?>
                    <div class="text-5xl font-bold opacity-60">Listo para marcar…</div>
                    <div class="mt-2 text-xl opacity-50">Cuando alguien marque, aquí verás la bienvenida o el motivo del
                        rechazo.</div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            
            <aside class="col-span-12 lg:col-span-5">
                <div class="rounded-2xl border shadow-sm
                            bg-white border-gray-200 text-gray-900
                            dark:bg-gray-900 dark:border-gray-800 dark:text-white p-6 md:p-8">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg md:text-xl font-semibold">Alertas de salida</h3>
                        <span class="text-xs md:text-sm opacity-70">
                            Umbral: <?php echo e(config('maxpower.kiosk.warn_minutes', 5)); ?> min
                        </span>
                    </div>

                    <!--[if BLOCK]><![endif]--><?php if($this->advertencias->isEmpty()): ?>
                        <p class="mt-5 text-sm md:text-base opacity-70">Sin alertas por ahora.</p>
                    <?php else: ?>
                        <div class="mt-5 space-y-4">
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->advertencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="rounded-2xl p-5
                                                    bg-amber-500/10 border border-amber-400/30">
                                    <div class="text-lg md:text-xl font-semibold">
                                        ⏳ <?php echo e($a->nombre_completo); ?>

                                    </div>
                                    <div class="mt-1 text-xs md:text-sm opacity-80">
                                        <?php echo e(ucfirst($a->rol)); ?> • <?php echo e(strtoupper($a->tipo_asistencia)); ?>

                                        • Entró: <?php echo e(optional($a->hora_entrada)->format('H:i')); ?>

                                    </div>
                                    <div class="mt-3 text-2xl md:text-3xl font-extrabold text-amber-300">
                                        Le quedan <?php echo e($a->min_restantes); ?> min
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <div class="mt-6 text-[11px] md:text-xs opacity-60">
                        Las asistencias se cierran solas si no marcan salida (cron de autocierre activo).
                    </div>
                </div>
            </aside>
        </main>

        
        <footer class="px-6 md:px-8 py-3 border-t border-gray-200/70 dark:border-gray-800/70
                        bg-white/60 dark:bg-gray-900/60 backdrop-blur">
            <div class="text-xs md:text-sm opacity-70">
                <?php echo e(now()->format('d/m/Y H:i:s')); ?> • MAXPOWERGYM
            </div>
        </footer>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/filament/pages/kiosk-asistencias.blade.php ENDPATH**/ ?>