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

        <h2 class="text-xl font-bold text-primary">¡Hola <?php echo e(auth()->user()->name); ?>!</h2>

        <div class="p-4 border rounded-lg shadow">
            <h3 class="text-lg font-semibold text-orange-600">📋 Plan Actual</h3>
            <!--[if BLOCK]><![endif]--><?php if($cliente->planesCliente->last()): ?>
                <p><strong>Plan:</strong> <?php echo e($cliente->planesCliente->last()->plan->nombre); ?></p>
                <p><strong>Inicio:</strong> <?php echo e($cliente->planesCliente->last()->fecha_inicio->format('d/m/Y')); ?></p>
                <p><strong>Fin:</strong> <?php echo e($cliente->planesCliente->last()->fecha_final->format('d/m/Y')); ?></p>
                <p><strong>Días restantes:</strong>
                    <?php echo e(now()->diffInDays($cliente->planesCliente->last()->fecha_final, false)); ?> días</p>
            <?php else: ?>
                <p>No tienes ningún plan activo.</p>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <div class="p-4 border rounded-lg shadow">
            <h3 class="text-lg font-semibold text-orange-600">🕒 Asistencias recientes</h3>
            <!--[if BLOCK]><![endif]--><?php if($cliente->asistencias->count()): ?>
                <ul class="list-disc ml-5">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $cliente->asistencias->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($a->fecha); ?> - <?php echo e($a->hora_entrada); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </ul>
            <?php else: ?>
                <p>No se encontraron asistencias recientes.</p>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <div class="p-4 border rounded-lg shadow">
            <h3 class="text-lg font-semibold text-orange-600">🏋️‍♂️ Sesiones Adicionales</h3>
            <!--[if BLOCK]><![endif]--><?php if($cliente->sesionesAdicionales->count()): ?>
                <ul class="list-disc ml-5">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $cliente->sesionesAdicionales->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($s->fecha); ?> - <?php echo e($s->tipo_sesion); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </ul>
            <?php else: ?>
                <p>No tienes sesiones adicionales registradas.</p>
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/filament/pages/cliente-dashboard.blade.php ENDPATH**/ ?>