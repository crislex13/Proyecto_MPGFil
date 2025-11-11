<?php if (isset($component)) { $__componentOriginalbe23554f7bded3778895289146189db7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbe23554f7bded3778895289146189db7 = $attributes; } ?>
<?php $component = Filament\View\LegacyComponents\Page::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Filament\View\LegacyComponents\Page::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <style>
        :root { --mpg-orange:#FF6600; --mpg-dark:#4E5054; --mpg-black:#000; }

        @font-face { font-family:'Cornero';  src:url('/fonts/Cornero-Regular.woff2')  format('woff2'); font-display:swap; }
        @font-face { font-family:'Geometos'; src:url('/fonts/Geometos-Regular.woff2') format('woff2'); font-display:swap; }
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

        /* Badge naranja (texto blanco), funciona en ambos temas */
        .badge-mpg{
            display:inline-flex;align-items:center;gap:.35rem;
            padding:.38rem .75rem;border-radius:9999px;
            font:700 11px/1 Poppins,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Arial;
            background:var(--mpg-orange);color:#fff;border:none;text-shadow:none;-webkit-text-stroke:0;
        }
    </style>

    <?php
        $planActual = $cliente->planesCliente->last();
        $diasRestantes = $planActual ? now()->startOfDay()->diffInDays($planActual->fecha_final->startOfDay(), false) : null;

        $validas = $cliente->asistencias
            ->whereIn('estado', ['puntual','atrasado','permiso'])
            ->sortByDesc(fn($a) => \Carbon\Carbon::parse($a->fecha)->format('Y-m-d').' '.\Carbon\Carbon::parse($a->hora_entrada)->format('H:i:s'))
            ->values();

        $validasCount = $validas->count();
        $validasTop5  = $validas->take(5);
    ?>

    <div class="space-y-6 font-[Poppins]">

        
        <section class="rounded-2xl border p-6 shadow-sm
                        bg-white border-gray-200 text-gray-900
                        dark:bg-gray-900 dark:border-gray-800 dark:text-white">
            <div class="text-center space-y-2">
                <h2 class="text-2xl md:text-3xl font-bold tracking-tight"
                    style="font-family:'Cornero','Poppins',sans-serif">
                    ¡Hola <?php echo e(auth()->user()->name); ?>!
                </h2>
                <p class="text-sm md:text-base/6 text-gray-700 dark:text-gray-300">
                    ¡Acepta el desafío, rompe los límites! 🥇
                </p>
                <div class="pt-1">
                    <span class="badge-mpg">Cliente</span>
                </div>
            </div>

            <div class="mt-4 mx-auto h-[3px] w-32 rounded-full
                        bg-gradient-to-r from-[color:var(--mpg-orange)] to-[color:var(--mpg-black)]">
            </div>
        </section>

        
        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <div class="rounded-2xl border p-6 shadow-sm text-center
                        bg-white border-gray-200 text-gray-900
                        dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                <div class="flex items-center justify-center gap-2">
                    <h3 class="text-[12px] font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Plan</h3>
                    <span class="badge-mpg">Actual</span>
                </div>
                <div class="mt-3 text-3xl font-bold tracking-tight">
                    <?php echo e($planActual?->plan?->nombre ?? 'Sin plan'); ?>

                </div>
                <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                    <!--[if BLOCK]><![endif]--><?php if($planActual): ?>
                        <?php echo e($planActual->fecha_inicio->format('d/m/Y')); ?> — <?php echo e($planActual->fecha_final->format('d/m/Y')); ?>

                    <?php else: ?>
                        Adquiere un plan para comenzar.
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                <!--[if BLOCK]><![endif]--><?php if(!is_null($diasRestantes)): ?>
                    <div class="mt-4">
                        <div class="mx-auto h-[3px] w-32 rounded-full
                                    bg-gradient-to-r from-[color:var(--mpg-orange)] to-[color:var(--mpg-black)]"></div>
                        <div class="mt-3 text-sm">
                            <span class="font-semibold text-gray-800 dark:text-gray-200">
                                Días restantes: <?php echo e($diasRestantes); ?> <?php echo e($diasRestantes===1?'día':'días'); ?>

                            </span>
                        </div>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            
            <div class="rounded-2xl border p-6 shadow-sm text-center
                        bg-white border-gray-200 text-gray-900
                        dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                <div class="flex items-center justify-center gap-2">
                    <h3 class="text-[12px] font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Asistencias</h3>
                    <span class="badge-mpg">Válidas</span>
                </div>
                <div class="mt-3 text-4xl font-extrabold tracking-tight">
                    <?php echo e($validasCount); ?>

                </div>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">Entradas reales registradas.</p>
            </div>

            
            <div class="rounded-2xl border p-6 shadow-sm text-center
                        bg-white border-gray-200 text-gray-900
                        dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                <div class="flex items-center justify-center gap-2">
                    <h3 class="text-[12px] font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                        Sesiones Adicionales</h3>
                    <span class="badge-mpg">Hoy & Próximas</span>
                </div>
                <div class="mt-3 text-4xl font-extrabold tracking-tight">
                    <?php echo e($cliente->sesionesAdicionales->count()); ?>

                </div>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">Registros de tu cuenta.</p>
            </div>
        </section>

        
        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <div class="rounded-2xl border p-6 shadow-sm text-center
                        bg-white border-gray-200 text-gray-900
                        dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                <h3 class="text-lg font-semibold"
                    style="font-family:'Geometos','Poppins',sans-serif">
                    📋 Plan Actual
                </h3>
                <div class="mt-3 text-sm text-gray-800 dark:text-gray-300 space-y-1">
                    <!--[if BLOCK]><![endif]--><?php if($planActual): ?>
                        <p><strong>Plan:</strong> <?php echo e($planActual->plan->nombre); ?></p>
                        <p><strong>Inicio:</strong> <?php echo e($planActual->fecha_inicio->format('d/m/Y')); ?></p>
                        <p><strong>Fin:</strong> <?php echo e($planActual->fecha_final->format('d/m/Y')); ?></p>
                        <p class="mt-2"><span class="badge-mpg">Días restantes: <?php echo e($diasRestantes); ?></span></p>
                    <?php else: ?>
                        <p>No tienes ningún plan activo.</p>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>

            
            <div class="rounded-2xl border p-6 shadow-sm text-center
                        bg-white border-gray-200 text-gray-900
                        dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                <h3 class="text-lg font-semibold"
                    style="font-family:'Geometos','Poppins',sans-serif">
                    🕒 Asistencias recientes
                </h3>
                <div class="mt-3 text-sm text-gray-800 dark:text-gray-300">
                    <!--[if BLOCK]><![endif]--><?php if($validasTop5->count()): ?>
                        <ul class="inline-block text-left space-y-1">
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $validasTop5; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        <?php echo e(\Carbon\Carbon::parse($a->fecha)->format('d/m/Y')); ?>

                                    </span>
                                    — <?php echo e(\Carbon\Carbon::parse($a->hora_entrada)->format('H:i')); ?>

                                    <span class="badge-mpg ml-2"><?php echo e(strtoupper($a->estado)); ?></span>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </ul>
                    <?php else: ?>
                        <p>No se encontraron asistencias válidas recientes.</p>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>

            
            <div class="rounded-2xl border p-6 shadow-sm text-center
                        bg-white border-gray-200 text-gray-900
                        dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                <h3 class="text-lg font-semibold"
                    style="font-family:'Geometos','Poppins',sans-serif">
                    🏋️‍♂️ Sesiones Adicionales
                </h3>
                <div class="mt-3 text-sm text-gray-800 dark:text-gray-300">
                    <!--[if BLOCK]><![endif]--><?php if($cliente->sesionesAdicionales->count()): ?>
                        <ul class="inline-block text-left space-y-1">
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $cliente->sesionesAdicionales->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        <?php echo e(\Carbon\Carbon::parse($s->fecha)->format('d/m/Y')); ?>

                                    </span>
                                    — <?php echo e($s->tipo_sesion); ?>

                                    <!--[if BLOCK]><![endif]--><?php if($s->hora_inicio && $s->hora_fin): ?>
                                        <span class="ml-1 text-gray-600 dark:text-gray-400">
                                            <?php echo e(\Carbon\Carbon::parse($s->hora_inicio)->format('H:i')); ?>–<?php echo e(\Carbon\Carbon::parse($s->hora_fin)->format('H:i')); ?>

                                        </span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </ul>
                    <?php else: ?>
                        <p>No tienes sesiones adicionales registradas.</p>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </section>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbe23554f7bded3778895289146189db7)): ?>
<?php $attributes = $__attributesOriginalbe23554f7bded3778895289146189db7; ?>
<?php unset($__attributesOriginalbe23554f7bded3778895289146189db7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbe23554f7bded3778895289146189db7)): ?>
<?php $component = $__componentOriginalbe23554f7bded3778895289146189db7; ?>
<?php unset($__componentOriginalbe23554f7bded3778895289146189db7); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/filament/pages/cliente-dashboard.blade.php ENDPATH**/ ?>