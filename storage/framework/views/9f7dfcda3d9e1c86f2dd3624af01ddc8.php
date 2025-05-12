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
    <div class="space-y-4">

        <h2 class="text-xl font-bold text-primary">¡Bienvenido <?php echo e(auth()->user()->name); ?>!</h2>

        <div class="p-4 border rounded-lg shadow">
            <h3 class="text-lg font-semibold text-orange-600">📅 Turnos de la Semana</h3>
            <!--[if BLOCK]><![endif]--><?php if($instructor->turnos->count()): ?>
                <ul class="list-disc ml-5">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $instructor->turnos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $turno): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($turno->dia); ?> - <?php echo e($turno->nombre); ?> (<?php echo e($turno->hora_inicio); ?> - <?php echo e($turno->hora_fin); ?>)</li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </ul>
            <?php else: ?>
                <p>No tienes turnos asignados esta semana.</p>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <div class="p-4 border rounded-lg shadow">
            <h3 class="text-lg font-semibold text-orange-600">🗓️ Asistencias Recientes</h3>
            <!--[if BLOCK]><![endif]--><?php if($instructor->asistencias->count()): ?>
                <ul class="list-disc ml-5">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $instructor->asistencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asistencia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($asistencia->fecha); ?> - <?php echo e($asistencia->hora_entrada); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </ul>
            <?php else: ?>
                <p>No se encontraron asistencias recientes.</p>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/filament/pages/instructor-dashboard.blade.php ENDPATH**/ ?>