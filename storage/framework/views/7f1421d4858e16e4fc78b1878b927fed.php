<?php
    $personalId = data_get($this->data, 'personal_id');
    $personal = \App\Models\Personal::find($personalId);
?>

<!--[if BLOCK]><![endif]--><?php if($personal): ?>
    <div class="w-full max-w-md mx-auto bg-white/5 p-4 rounded-xl shadow border border-gray-700 flex flex-col items-center space-y-4">
        <!--[if BLOCK]><![endif]--><?php if($personal->foto): ?>
            <img
                src="<?php echo e(\Illuminate\Support\Facades\Storage::url($personal->foto)); ?>"
                alt="Foto del personal"
                class="w-40 h-40 object-cover rounded-full border border-gray-500 shadow-md transition hover:scale-105 duration-300 ease-in-out"
            >
        <?php else: ?>
            <div class="w-40 h-40 flex items-center justify-center rounded-full bg-gray-800 text-gray-400 text-sm border border-gray-500">
                Sin Foto
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <div class="text-center">
            <h2 class="text-lg font-semibold text-white"><?php echo e($personal->nombre_completo); ?></h2>
            <p class="text-sm text-gray-400">
                Cargo: <?php echo e($personal->cargo); ?><br>
                Salario: <span class="text-green-400 font-semibold"><?php echo e(number_format($personal->salario, 2)); ?> Bs.</span>
            </p>
        </div>
    </div>
<?php else: ?>
    <div class="text-center italic text-gray-500">Sin información del personal.</div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]--><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/components/personal-foto.blade.php ENDPATH**/ ?>