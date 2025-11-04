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

        .badge-mpg{
            display:inline-flex;align-items:center;gap:.35rem;
            padding:.38rem .75rem;border-radius:9999px;
            font:700 11px/1 Poppins,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Arial;
            background:var(--mpg-orange);color:#fff;border:none;text-shadow:none;-webkit-text-stroke:0;
        }
    </style>

    <?php
        // Top 5 asistencias más recientes del instructor
        $asisTop5 = $instructor->asistencias
            ->sortByDesc(fn($a) => \Carbon\Carbon::parse($a->fecha)->format('Y-m-d').' '.\Carbon\Carbon::parse($a->hora_entrada)->format('H:i:s'))
            ->take(5)
            ->values();

        $totalTurnos = $instructor->turnos->count();
        $totalAsistencias = $instructor->asistencias->count();
    ?>

    <div class="space-y-6 font-[Poppins]">

        
        <section class="rounded-2xl border p-6 shadow-sm
                        bg-white border-gray-200 text-gray-900
                        dark:bg-gray-900 dark:border-gray-800 dark:text-white">
            <div class="text-center space-y-2">
                <h2 class="text-2xl md:text-3xl font-bold tracking-tight"
                    style="font-family:'Cornero','Poppins',sans-serif">
                    ¡Bienvenido <?php echo e(auth()->user()->name); ?>!
                </h2>
                <p class="text-sm md:text-base/6 text-gray-700 dark:text-gray-300">
                    ¡Acepta el desafío, rompe los límites! 🥇
                </p>
                <div class="pt-1">
                    <span class="badge-mpg">Instructor</span>
                </div>
            </div>

            <div class="mt-4 mx-auto h-[3px] w-32 rounded-full
                        bg-gradient-to-r from-[color:var(--mpg-orange)] to-[color:var(--mpg-black)]">
            </div>
        </section>

        
        <section class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-2xl border p-6 shadow-sm text-center
                        bg-white border-gray-200 text-gray-900
                        dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                <div class="flex items-center justify-center gap-2">
                    <h3 class="text-[12px] font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                        Turnos de la semana
                    </h3>
                    <span class="badge-mpg">Total</span>
                </div>
                <div class="mt-3 text-4xl font-extrabold tracking-tight">
                    <?php echo e($totalTurnos); ?>

                </div>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">Turnos asignados actualmente.</p>
            </div>

            <div class="rounded-2xl border p-6 shadow-sm text-center
                        bg-white border-gray-200 text-gray-900
                        dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                <div class="flex items-center justify-center gap-2">
                    <h3 class="text-[12px] font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                        Asistencias
                    </h3>
                    <span class="badge-mpg">Histórico</span>
                </div>
                <div class="mt-3 text-4xl font-extrabold tracking-tight">
                    <?php echo e($totalAsistencias); ?>

                </div>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">Registros en tu historial.</p>
            </div>
        </section>

        
        <section class="grid grid-cols-1 md:grid-cols-2 gap-4">

            
            <div class="rounded-2xl border p-6 shadow-sm
                        bg-white border-gray-200 text-gray-900
                        dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold" style="font-family:'Geometos','Poppins',sans-serif">
                        📅 Turnos de la Semana
                    </h3>
                    <span class="badge-mpg">Activo</span>
                </div>

                <div class="mt-3 text-sm text-gray-800 dark:text-gray-300">
                    <!--[if BLOCK]><![endif]--><?php if($instructor->turnos->count()): ?>
                        <ul class="list-disc ml-6 space-y-1">
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $instructor->turnos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $turno): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        <?php echo e($turno->dia); ?>

                                    </span>
                                    — <?php echo e($turno->nombre); ?>

                                    (<?php echo e($turno->hora_inicio); ?> - <?php echo e($turno->hora_fin); ?>)
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </ul>
                    <?php else: ?>
                        <p>No tienes turnos asignados esta semana.</p>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>

            
            <div class="rounded-2xl border p-6 shadow-sm
                        bg-white border-gray-200 text-gray-900
                        dark:bg-gray-900 dark:border-gray-800 dark:text-white">
                <h3 class="text-lg font-semibold" style="font-family:'Geometos','Poppins',sans-serif">
                    🗓️ Asistencias Recientes
                </h3>

                <div class="mt-3 text-sm text-gray-800 dark:text-gray-300">
                    <!--[if BLOCK]><![endif]--><?php if($asisTop5->count()): ?>
                        <ul class="list-disc ml-6 space-y-1">
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $asisTop5; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asistencia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        <?php echo e(\Carbon\Carbon::parse($asistencia->fecha)->format('d/m/Y')); ?>

                                    </span>
                                    — <?php echo e(\Carbon\Carbon::parse($asistencia->hora_entrada)->format('H:i')); ?>

                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </ul>
                    <?php else: ?>
                        <p>No se encontraron asistencias recientes.</p>
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
<?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/filament/pages/instructor-dashboard.blade.php ENDPATH**/ ?>