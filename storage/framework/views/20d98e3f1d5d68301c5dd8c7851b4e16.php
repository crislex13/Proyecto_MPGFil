<?php
    $imageSize = 'w-20 h-20'; // Ajusta aquí el tamaño
?>

<?php if (isset($component)) { $__componentOriginal9b945b32438afb742355861768089b04 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9b945b32438afb742355861768089b04 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <!--[if BLOCK]><![endif]--><?php if($instructor): ?>
        <div class="flex flex-col items-center text-center space-y-2">
            <img
                src="<?php echo e(asset('storage/' . $instructor->foto)); ?>"
                alt="Foto del Instructor"
                class="<?php echo e($imageSize); ?> rounded-full object-cover shadow-md mb-2"
            />

            <h2 class="text-base font-bold text-gray-100">
                <?php echo e($instructor->nombre); ?> <?php echo e($instructor->apellido_paterno); ?> <?php echo e($instructor->apellido_materno); ?>

            </h2>
            <p class="text-xs text-gray-400">Instructor más cotizado del mes</p>

            <div class="text-sm text-gray-300 mt-1 space-y-1">
                <p>📅 <strong><?php echo e($totalSesiones); ?></strong> sesiones</p>
                <p>💸 <strong><?php echo e(number_format($totalGanancias, 2)); ?> Bs</strong></p>
            </div>
        </div>
    <?php else: ?>
        <p class="text-center text-gray-500">No hay sesiones registradas este mes.</p>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9b945b32438afb742355861768089b04)): ?>
<?php $attributes = $__attributesOriginal9b945b32438afb742355861768089b04; ?>
<?php unset($__attributesOriginal9b945b32438afb742355861768089b04); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9b945b32438afb742355861768089b04)): ?>
<?php $component = $__componentOriginal9b945b32438afb742355861768089b04; ?>
<?php unset($__componentOriginal9b945b32438afb742355861768089b04); ?>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/filament/widgets/instructor-top-widget.blade.php ENDPATH**/ ?>