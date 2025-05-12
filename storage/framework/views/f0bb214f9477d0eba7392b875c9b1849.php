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
    <div class="space-y-6">

        
        <div
            class="rounded-xl bg-[#FF6600] px-6 py-4 shadow-lg text-white flex flex-col md:flex-row items-center justify-between">
            <div class="text-xl font-bold uppercase tracking-wide">
                ¡Acepta el desafío, rompe los límites!
            </div>
            <div class="text-sm mt-2 md:mt-0 italic text-white/80">
                MAXPOWERGYM — Dashboard combinado
            </div>
        </div>

        
        <h2 class="text-2xl font-bold text-[#4E5054]">Hola <?php echo e(auth()->user()->name); ?> 👋</h2>

        
        <!--[if BLOCK]><![endif]--><?php if($cliente): ?>
            <div class="p-4 border border-[#FF6600] rounded-lg shadow-md bg-white/5">
                <h3 class="text-lg font-semibold text-[#FF6600]">📋 Plan Actual</h3>
                <!--[if BLOCK]><![endif]--><?php if($cliente->planesCliente->last()): ?>
                    <ul class="mt-2 list-disc ml-6 text-white">
                        <li><strong>Plan:</strong> <?php echo e($cliente->planesCliente->last()->plan->nombre); ?></li>
                        <li><strong>Inicio:</strong> <?php echo e($cliente->planesCliente->last()->fecha_inicio->format('d/m/Y')); ?></li>
                        <li><strong>Fin:</strong> <?php echo e($cliente->planesCliente->last()->fecha_final->format('d/m/Y')); ?></li>
                        <li><strong>Días restantes:</strong>
                            <?php echo e(now()->diffInDays($cliente->planesCliente->last()->fecha_final, false)); ?> días</li>
                    </ul>
                <?php else: ?>
                    <p class="text-white mt-2">No tienes ningún plan activo.</p>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            
            <div class="p-4 border border-[#FF6600] rounded-lg shadow-md bg-white/5">
                <h3 class="text-lg font-semibold text-[#FF6600]">🕒 Asistencias recientes</h3>
                <!--[if BLOCK]><![endif]--><?php if($cliente->asistencias->count()): ?>
                    <ul class="mt-2 list-disc ml-6 text-white">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $cliente->asistencias->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($a->fecha); ?> - <?php echo e($a->hora_entrada); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </ul>
                <?php else: ?>
                    <p class="text-white mt-2">No se encontraron asistencias recientes.</p>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            
            <div class="p-4 border border-[#FF6600] rounded-lg shadow-md bg-white/5">
                <h3 class="text-lg font-semibold text-[#FF6600]">🏋️‍♂️ Sesiones Adicionales</h3>
                <!--[if BLOCK]><![endif]--><?php if($cliente->sesionesAdicionales->count()): ?>
                    <ul class="mt-2 list-disc ml-6 text-white">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $cliente->sesionesAdicionales->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($s->fecha); ?> - <?php echo e($s->tipo_sesion); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </ul>
                <?php else: ?>
                    <p class="text-white mt-2">No tienes sesiones adicionales registradas.</p>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        
        <!--[if BLOCK]><![endif]--><?php if($instructor): ?>
            <div class="p-4 border border-[#4E5054] rounded-lg shadow-md bg-white/5">
                <h3 class="text-lg font-semibold text-[#4E5054]">📅 Turnos de la Semana</h3>
                <!--[if BLOCK]><![endif]--><?php if($instructor->turnos->count()): ?>
                    <ul class="mt-2 list-disc ml-6 text-white">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $instructor->turnos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $turno): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($turno->dia); ?> - <?php echo e($turno->nombre); ?> (<?php echo e($turno->hora_inicio); ?> - <?php echo e($turno->hora_fin); ?>)</li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </ul>
                <?php else: ?>
                    <p class="text-white mt-2">No tienes turnos asignados esta semana.</p>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            
            <div class="p-4 border border-[#4E5054] rounded-lg shadow-md bg-white/5">
                <h3 class="text-lg font-semibold text-[#4E5054]">🗓️ Asistencias Recientes</h3>
                <!--[if BLOCK]><![endif]--><?php if($instructor->asistencias->count()): ?>
                    <ul class="mt-2 list-disc ml-6 text-white">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $instructor->asistencias->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asistencia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($asistencia->fecha); ?> - <?php echo e($asistencia->hora_entrada); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </ul>
                <?php else: ?>
                    <p class="text-white mt-2">No se encontraron asistencias recientes.</p>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/filament/pages/dashboard-multiples.blade.php ENDPATH**/ ?>