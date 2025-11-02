
<?php if (isset($component)) { $__componentOriginalf45da69382bf4ac45a50b496dc82aa9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf45da69382bf4ac45a50b496dc82aa9a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.simple','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page.simple'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <!--[if BLOCK]><![endif]--><?php if(filament()->hasRegistration()): ?>
         <?php $__env->slot('subheading', null, []); ?> 
            <?php echo e(__('filament-panels::pages/auth/login.actions.register.before')); ?>

            <?php echo e($this->registerAction); ?>

         <?php $__env->endSlot(); ?>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <?php echo e(\Filament\Support\Facades\FilamentView::renderHook(
    \Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
    scopes: $this->getRenderHookScopes()
)); ?>



    
    <style>
        :root {
            --brand: #FF6600;
            /* naranja */
            --brand-600: #E65C00;
            /* hover */
            --ink: #4E5054;
            /* gris oscuro */
        }

        /* Botón primario de Filament (Entrar) */
        .fi-btn.fi-color-primary {
            background-color: var(--brand) !important;
            border-color: var(--brand) !important;
            color: #fff !important;
        }

        .fi-btn.fi-color-primary:hover {
            background-color: var(--brand-600) !important;
            border-color: var(--brand-600) !important;
        }
    </style>

    <?php if (isset($component)) { $__componentOriginald09a0ea6d62fc9155b01d885c3fdffb3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald09a0ea6d62fc9155b01d885c3fdffb3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.form.index','data' => ['wire:submit.prevent' => 'authenticate']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:submit.prevent' => 'authenticate']); ?>
        <?php echo csrf_field(); ?>

        
        <!--[if BLOCK]><![endif]--><?php if(
                session('auth_error_banner')
                || ($errors->has('data.username') && $errors->first('data.username') === 'Usuario o contraseña incorrectos.')
                || ($errors->has('data.password') && $errors->first('data.password') === 'Usuario o contraseña incorrectos.')
            ): ?>
            <div role="alert" aria-live="assertive"
                class="mb-3 rounded-lg border border-danger-500 bg-danger-600/15 px-3 py-2 text-sm text-danger-200">
                <strong>Acceso denegado:</strong> Usuario o contraseña incorrectos.
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        
        <!--[if BLOCK]><![endif]--><?php if($errors->has('data.username') && str_contains($errors->first('data.username'), 'Demasiados intentos')): ?>
            <div role="alert" aria-live="assertive"
                class="mb-3 rounded-lg border border-warning-500 bg-warning-600/15 px-3 py-2 text-sm text-warning-200">
                <strong>Bloqueo temporal:</strong> <?php echo e($errors->first('data.username')); ?>

            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        
        <!--[if BLOCK]><![endif]--><?php if($errors->any() && !$errors->has('data.username') && !$errors->has('data.password')): ?>
            <div role="alert" aria-live="polite"
                class="mb-3 rounded-lg border border-danger-500 bg-danger-600/15 px-3 py-2 text-sm text-danger-200">
                Revisa los campos marcados e inténtalo nuevamente.
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <div class="space-y-4">
            
            <div class="space-y-1">
                <label for="username" class="block text-sm font-medium text-[#FF6600]">
                    Nombre de usuario
                </label>
                <input wire:model.defer="data.username" type="text" name="username" id="username"
                    autocomplete="username" required autofocus placeholder="Ej: admin_258877" <?php $__errorArgs = ['data.username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    aria-invalid="true" aria-describedby="err-username" <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    class="block w-full rounded-lg border border-[#4E5054] bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400
                           focus:ring-2 focus:ring-[#FF6600] focus:border-[#FF6600] hover:border-[#FF6600]/60 transition" />
                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['data.username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p id="err-username" class="text-sm text-danger-600 mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            
            <div class="space-y-1">
                <label for="password" class="block text-sm font-medium text-[#FF6600]">
                    Contraseña
                </label>
                <input wire:model.defer="data.password" type="password" name="password" id="password"
                    autocomplete="current-password" required placeholder="••••••••" <?php $__errorArgs = ['data.password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    aria-invalid="true" aria-describedby="err-password" <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    class="block w-full rounded-lg border border-[#4E5054] bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400
                           focus:ring-2 focus:ring-[#FF6600] focus:border-[#FF6600] hover:border-[#FF6600]/60 transition" />
                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['data.password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p id="err-password" class="text-sm text-danger-600 mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            
            <div class="space-y-1 mt-2">
                <label for="captcha" class="block text-sm font-medium text-[#FF6600]">
                    Verificación
                </label>

                <div class="flex items-center gap-3">
                    <img id="captchaImg" src="<?php echo e(captcha_src('flat')); ?>" alt="Imagen de verificación"
                        class="rounded-lg border border-[#4E5054] h-20 bg-white" />
                    <button type="button"
                        onclick="document.getElementById('captchaImg').src='<?php echo e(captcha_src('flat')); ?>&r=' + Date.now()"
                        class="px-3 py-2 rounded-lg border border-[#FF6600] text-[#FF6600]
                               hover:bg-[#FF6600]/10 focus:outline-none focus:ring-2 focus:ring-[#FF6600]"
                        title="Actualizar verificación" aria-label="Actualizar verificación">
                        ↻
                    </button>
                </div>

                <input wire:model.defer="data.captcha" type="text" id="captcha" name="captcha" inputmode="text"
                    autocomplete="off" placeholder="Escribe las letras de la imagen" <?php $__errorArgs = ['data.captcha'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    aria-invalid="true" aria-describedby="err-captcha" <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    class="block w-full rounded-lg border border-[#4E5054] bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400
                           focus:ring-2 focus:ring-[#FF6600] focus:border-[#FF6600] hover:border-[#FF6600]/60 transition" />
                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['data.captcha'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p id="err-captcha" class="text-sm text-danger-600 mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>

        <?php if (isset($component)) { $__componentOriginal742ef35d02cb00943edd9ad8ebf61966 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal742ef35d02cb00943edd9ad8ebf61966 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.form.actions','data' => ['actions' => $this->getCachedFormActions(),'fullWidth' => $this->hasFullWidthFormActions()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::form.actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->getCachedFormActions()),'full-width' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->hasFullWidthFormActions())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal742ef35d02cb00943edd9ad8ebf61966)): ?>
<?php $attributes = $__attributesOriginal742ef35d02cb00943edd9ad8ebf61966; ?>
<?php unset($__attributesOriginal742ef35d02cb00943edd9ad8ebf61966); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal742ef35d02cb00943edd9ad8ebf61966)): ?>
<?php $component = $__componentOriginal742ef35d02cb00943edd9ad8ebf61966; ?>
<?php unset($__componentOriginal742ef35d02cb00943edd9ad8ebf61966); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald09a0ea6d62fc9155b01d885c3fdffb3)): ?>
<?php $attributes = $__attributesOriginald09a0ea6d62fc9155b01d885c3fdffb3; ?>
<?php unset($__attributesOriginald09a0ea6d62fc9155b01d885c3fdffb3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald09a0ea6d62fc9155b01d885c3fdffb3)): ?>
<?php $component = $__componentOriginald09a0ea6d62fc9155b01d885c3fdffb3; ?>
<?php unset($__componentOriginald09a0ea6d62fc9155b01d885c3fdffb3); ?>
<?php endif; ?>

    <?php echo e(\Filament\Support\Facades\FilamentView::renderHook(
    \Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
    scopes: $this->getRenderHookScopes()
)); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf45da69382bf4ac45a50b496dc82aa9a)): ?>
<?php $attributes = $__attributesOriginalf45da69382bf4ac45a50b496dc82aa9a; ?>
<?php unset($__attributesOriginalf45da69382bf4ac45a50b496dc82aa9a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf45da69382bf4ac45a50b496dc82aa9a)): ?>
<?php $component = $__componentOriginalf45da69382bf4ac45a50b496dc82aa9a; ?>
<?php unset($__componentOriginalf45da69382bf4ac45a50b496dc82aa9a); ?>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/vendor/filament-panels/pages/auth/login.blade.php ENDPATH**/ ?>