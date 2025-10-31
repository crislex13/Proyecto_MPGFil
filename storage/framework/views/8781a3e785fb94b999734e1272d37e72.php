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
    <?php $__env->stopPush(); ?>

    <div class="min-h-screen w-full bg-black text-white flex flex-col">

        
        <div class="px-8 py-4 border-b border-white/10 flex items-center justify-between">
            <div class="text-2xl font-semibold tracking-wide">MAXPOWERGYM • Monitor de Accesos</div>
            <div class="text-sm opacity-70">Actualiza cada <?php echo e(config('maxpower.kiosk.poll_seconds', 3)); ?>s</div>
        </div>

        <div class="grid grid-cols-12 gap-6 p-8 flex-1">
            
            <div class="col-span-7 bg-white/5 rounded-2xl p-8 flex flex-col justify-center">
                <?php $evt = $this->ultimoEvento; ?>

                <!--[if BLOCK]><![endif]--><?php if($evt): ?>
                    <?php
                        $esDenegado = $evt->estado === 'acceso_denegado';
                        $nombre = $evt->nombre_completo;
                        $rol    = $evt->rol; // Cliente / Personal
                        $hora   = optional($evt->hora_entrada)->format('H:i');
                    ?>

                    <!--[if BLOCK]><![endif]--><?php if(!$esDenegado): ?>
                        <div class="text-6xl font-bold text-emerald-400">¡BIENVENID<?php echo e($rol === 'Cliente' ? 'O' : 'A'); ?>!</div>
                        <div class="mt-4 text-4xl font-semibold"><?php echo e($nombre); ?></div>
                        <div class="mt-1 text-xl opacity-70"><?php echo e($rol); ?> • <?php echo e(strtoupper($evt->tipo_asistencia)); ?> • <?php echo e($hora); ?></div>

                        <!--[if BLOCK]><![endif]--><?php if($rol === 'Cliente'): ?>
                            
                            <div class="mt-8">
                                <div class="text-2xl font-semibold mb-3">Sesiones de hoy</div>
                                <!--[if BLOCK]><![endif]--><?php if($this->sesionesDeHoy->isEmpty()): ?>
                                    <div class="text-lg opacity-70">Sin sesiones registradas para hoy.</div>
                                <?php else: ?>
                                    <div class="space-y-2">
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->sesionesDeHoy; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="flex items-center justify-between bg-white/5 rounded-xl p-4">
                                                <div class="text-xl">🕒 <?php echo e(\Illuminate\Support\Str::of($s['hora_inicio'])->limit(5,'')); ?>–<?php echo e(\Illuminate\Support\Str::of($s['hora_fin'])->limit(5,'')); ?></div>
                                                <div class="text-sm opacity-70">ID #<?php echo e($s['id']); ?></div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <?php else: ?>
                        <div class="text-6xl font-bold text-rose-400">ACCESO DENEGADO</div>
                        <div class="mt-4 text-4xl font-semibold"><?php echo e($nombre); ?></div>
                        <div class="mt-1 text-xl opacity-70"><?php echo e($rol); ?> • <?php echo e(strtoupper($evt->tipo_asistencia)); ?> • <?php echo e($hora); ?></div>
                        <div class="mt-6 bg-rose-500/10 border border-rose-500/30 rounded-2xl p-6 text-2xl leading-snug">
                            <?php echo e($evt->observacion ?? 'Fuera de horario o sin condiciones para ingresar.'); ?>

                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <?php else: ?>
                    <div class="text-5xl font-bold opacity-60">Listo para marcar…</div>
                    <div class="mt-2 text-xl opacity-50">Cuando alguien marque, aquí verás la bienvenida o el motivo del rechazo.</div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            
            <div class="col-span-5 bg-white/5 rounded-2xl p-8">
                <div class="flex items-center justify-between">
                    <div class="text-3xl font-semibold">Alertas de salida</div>
                    <div class="text-sm opacity-70">Umbral: <?php echo e(config('maxpower.kiosk.warn_minutes', 5)); ?> min</div>
                </div>

                <!--[if BLOCK]><![endif]--><?php if($this->advertencias->isEmpty()): ?>
                    <div class="mt-6 text-lg opacity-60">Sin alertas por ahora.</div>
                <?php else: ?>
                    <div class="mt-6 space-y-4">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->advertencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded-2xl p-5 bg-amber-500/10 border border-amber-400/30">
                                <div class="text-2xl font-semibold">⏳ <?php echo e($a->nombre_completo); ?></div>
                                <div class="mt-1 text-sm opacity-70">
                                    <?php echo e(ucfirst($a->rol)); ?> • <?php echo e(strtoupper($a->tipo_asistencia)); ?> • Entró: <?php echo e(optional($a->hora_entrada)->format('H:i')); ?>

                                </div>
                                <div class="mt-3 text-3xl font-bold text-amber-300">
                                    Le quedan <?php echo e($a->min_restantes); ?> min
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <div class="mt-8 text-xs opacity-50">
                    Las asistencias se cierran solas si no marcan salida (cron de autocierre activo).
                </div>
            </div>
        </div>

        
        <div class="px-8 py-3 border-t border-white/10 text-sm opacity-50">
            <?php echo e(now()->format('d/m/Y H:i:s')); ?> • MAXPOWERGYM
        </div>
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
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/filament/pages/kiosk-asistencias.blade.php ENDPATH**/ ?>