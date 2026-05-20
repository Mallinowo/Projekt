<?php $__env->startSection('content'); ?>
<div class="bg-[#111118] border border-[#2a2a3a] rounded-2xl p-8 shadow-2xl">
    <div class="font-cinzel text-2xl font-bold text-purple-400 text-center tracking-widest mb-1" style="text-shadow:0 0 30px rgba(168,85,247,.5)">✦ ALTERMATCH</div>
    <p class="text-center text-[#5e5880] font-mono text-xs tracking-widest mb-6"><?php echo e(__('app.tagline')); ?></p>

    
    <div class="flex border border-[#2a2a3a] rounded-lg overflow-hidden mb-6">
        <a href="<?php echo e(route('login')); ?>" class="flex-1 text-center py-2 font-mono text-xs uppercase tracking-widest bg-purple-600 text-white"><?php echo e(__('auth.login')); ?></a>
        <a href="<?php echo e(route('register')); ?>" class="flex-1 text-center py-2 font-mono text-xs uppercase tracking-widest text-[#5e5880] hover:text-purple-400 transition-colors"><?php echo e(__('auth.register')); ?></a>
    </div>

    <form method="POST" action="<?php echo e(route('login')); ?>">
        <?php echo csrf_field(); ?>
        <div class="mb-4">
            <label class="block font-mono text-[.52rem] tracking-widest uppercase text-[#5e5880] mb-1"><?php echo e(__('auth.email')); ?></label>
            <input type="email" name="email" value="<?php echo e(old('email')); ?>" required
                class="w-full bg-[#16161f] border border-[#2a2a3a] rounded-lg px-3 py-2 text-[#e2e0f0] font-crimson text-base outline-none focus:border-purple-500 transition-colors"
                placeholder="twoj@email.com">
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-400 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="mb-5">
            <label class="block font-mono text-[.52rem] tracking-widest uppercase text-[#5e5880] mb-1"><?php echo e(__('auth.password')); ?></label>
            <input type="password" name="password" required
                class="w-full bg-[#16161f] border border-[#2a2a3a] rounded-lg px-3 py-2 text-[#e2e0f0] font-crimson text-base outline-none focus:border-purple-500 transition-colors"
                placeholder="••••••••">
        </div>
        <div class="flex items-center gap-2 mb-5">
            <input type="checkbox" name="remember" id="remember" class="accent-purple-500">
            <label for="remember" class="text-xs text-[#9991bb]"><?php echo e(__('auth.remember')); ?></label>
        </div>
        <button type="submit" class="w-full bg-gradient-to-br from-purple-500 to-violet-700 text-white py-3 rounded-lg font-mono text-xs uppercase tracking-widest shadow-[0_0_20px_rgba(168,85,247,.3)] hover:shadow-[0_0_35px_rgba(168,85,247,.55)] hover:-translate-y-0.5 transition-all">
            <?php echo e(__('auth.login')); ?>

        </button>
    </form>
    <p class="text-center mt-4 text-xs text-[#5e5880]"><?php echo e(__('auth.no_account')); ?> <a href="<?php echo e(route('register')); ?>" class="text-purple-400 hover:underline"><?php echo e(__('auth.register')); ?></a></p>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\malin\Desktop\altermatch\resources\views\auth\login.blade.php ENDPATH**/ ?>