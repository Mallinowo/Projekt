<?php $__env->startSection('content'); ?>
<div class="auth-card p-6 sm:p-8">
    <div class="text-center mb-7">
        <div class="auth-brand font-cinzel text-3xl font-bold text-purple-400 tracking-widest mb-2">ALTERMATCH</div>
        <p class="text-[#8f87b1] font-mono text-xs tracking-[.22em] uppercase"><?php echo e(__('app.tagline')); ?></p>
    </div>

    <div class="auth-tabs mb-7">
        <a href="<?php echo e(route('login')); ?>" class="auth-tab is-active"><?php echo e(__('auth.login')); ?></a>
        <a href="<?php echo e(route('register')); ?>" class="auth-tab"><?php echo e(__('auth.register')); ?></a>
    </div>

    <form method="POST" action="<?php echo e(route('login')); ?>" class="auth-form">
        <?php echo csrf_field(); ?>
        <div class="auth-field">
            <label class="field-label"><?php echo e(__('auth.email')); ?></label>
            <input
                type="email"
                name="email"
                value="<?php echo e(old('email')); ?>"
                required
                class="field-input auth-input"
                placeholder="twoj@email.com"
            >
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="auth-field">
            <label class="field-label"><?php echo e(__('auth.password')); ?></label>
            <input
                type="password"
                name="password"
                required
                class="field-input auth-input"
                placeholder="********"
            >
        </div>

        <div class="flex items-center gap-2 rounded-lg border border-[#2a2a3a] bg-[#0d0d14]/70 px-3 py-2.5">
            <input type="checkbox" name="remember" id="remember" class="accent-purple-500 w-4 h-4">
            <label for="remember" class="text-xs text-[#9991bb]"><?php echo e(__('auth.remember')); ?></label>
        </div>

        <button type="submit" class="auth-submit">
            <?php echo e(__('auth.login')); ?>

        </button>
    </form>

    <p class="auth-footer">
        <?php echo e(__('auth.no_account')); ?>

        <a href="<?php echo e(route('register')); ?>"><?php echo e(__('auth.register')); ?></a>
    </p>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/auth/login.blade.php ENDPATH**/ ?>