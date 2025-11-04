<?php
    $imageSize = 'w-20 h-20'; // Cambia aquí para ajustar el tamaño
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
    <!--[if BLOCK]><![endif]--><?php if($producto): ?>
        <div class="flex flex-col items-center text-center space-y-2">
            <!--[if BLOCK]><![endif]--><?php if($producto->imagen): ?>
                <img
                    src="<?php echo e(asset('storage/' . $producto->imagen)); ?>"
                    alt="Imagen del producto"
                    class="<?php echo e($imageSize); ?> rounded-xl object-cover shadow-md mb-2"
                />
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <h2 class="text-base font-bold text-gray-100">
                <?php echo e($producto->nombre); ?>

            </h2>
            <p class="text-xs text-gray-400">Producto más vendido del mes</p>

            <div class="text-sm text-gray-300 mt-1 space-y-1">
                <p>🛒 Vendidas: <strong><?php echo e($totalVendidas); ?></strong></p>
                <p>💰 Total: <strong><?php echo e(number_format($totalGenerado, 2)); ?> Bs</strong></p>
            </div>
        </div>
    <?php else: ?>
        <p class="text-center text-gray-500">No hay ventas registradas este mes.</p>
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/filament/widgets/producto-top-widget.blade.php ENDPATH**/ ?>