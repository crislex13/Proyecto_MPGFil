<input
    <?php echo e($attributes
            ->merge([
                'id' => $getId(),
                'type' => 'hidden',
                $applyStateBindingModifiers('wire:model') => $getStatePath(),
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)
            ->class(['fi-fo-hidden'])); ?>

/>
<?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\vendor\filament\forms\src\/../resources/views/components/hidden.blade.php ENDPATH**/ ?>