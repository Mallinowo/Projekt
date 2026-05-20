<?php $__env->startSection('content'); ?>
<div class="bg-[#111118] border border-[#2a2a3a] rounded-2xl p-8 shadow-2xl">
    <div class="font-cinzel text-2xl font-bold text-purple-400 text-center tracking-widest mb-1" style="text-shadow:0 0 30px rgba(168,85,247,.5)">✦ ALTERMATCH</div>
    <p class="text-center text-[#5e5880] font-mono text-xs tracking-widest mb-6"><?php echo e(__('app.tagline')); ?></p>

    <div class="flex border border-[#2a2a3a] rounded-lg overflow-hidden mb-6">
        <a href="<?php echo e(route('login')); ?>" class="flex-1 text-center py-2 font-mono text-xs uppercase tracking-widest text-[#5e5880] hover:text-purple-400 transition-colors"><?php echo e(__('auth.login')); ?></a>
        <a href="<?php echo e(route('register')); ?>" class="flex-1 text-center py-2 font-mono text-xs uppercase tracking-widest bg-purple-600 text-white"><?php echo e(__('auth.register')); ?></a>
    </div>

    <form method="POST" action="<?php echo e(route('register')); ?>">
        <?php echo csrf_field(); ?>
        <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
                <label class="field-label"><?php echo e(__('auth.username')); ?></label>
                <input type="text" name="name" value="<?php echo e(old('name')); ?>" required maxlength="30" class="field-input" placeholder="moonchild_x">
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="field-label"><?php echo e(__('auth.age')); ?></label>
                <input type="number" name="age" value="<?php echo e(old('age')); ?>" min="18" max="99" required class="field-input" placeholder="18">
                <?php $__errorArgs = ['age'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>
        <div class="mb-3">
            <label class="field-label"><?php echo e(__('auth.email')); ?></label>
            <input type="email" name="email" value="<?php echo e(old('email')); ?>" required class="field-input" placeholder="twoj@email.com">
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
                <label class="field-label"><?php echo e(__('auth.password')); ?></label>
                <input type="password" name="password" required minlength="6" class="field-input" placeholder="min. 6 znaków">
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="field-label"><?php echo e(__('auth.password_confirm')); ?></label>
                <input type="password" name="password_confirmation" required class="field-input" placeholder="powtórz hasło">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="field-label"><?php echo e(__('auth.city')); ?></label>
                <input type="text" name="city" value="<?php echo e(old('city')); ?>" required class="field-input" placeholder="Warszawa">
                <?php $__errorArgs = ['city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="field-label"><?php echo e(__('auth.subculture')); ?></label>
                <select name="subculture" required class="field-input">
                    <option value="">— <?php echo e(__('auth.choose')); ?> —</option>
                    <?php $__currentLoopData = ['emo'=>'🖤 Emo','scene'=>'🌈 Scene','goth'=>'🦇 Goth','punk'=>'⚡ Punk','metalhead'=>'🤘 Metalhead']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($val); ?>" <?php echo e(old('subculture')===$val?'selected':''); ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['subculture'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="field-label"><?php echo e(__('auth.gender')); ?></label>
                <select name="gender" required class="field-input">
                    <option value="">— <?php echo e(__('auth.choose')); ?> —</option>
                    <option value="female" <?php echo e(old('gender')==='female' ? 'selected' : ''); ?>><?php echo e(__('auth.gender_female')); ?></option>
                    <option value="male" <?php echo e(old('gender')==='male' ? 'selected' : ''); ?>><?php echo e(__('auth.gender_male')); ?></option>
                    <option value="nonbinary" <?php echo e(old('gender')==='nonbinary' ? 'selected' : ''); ?>><?php echo e(__('auth.gender_nonbinary')); ?></option>
                    <option value="other" <?php echo e(old('gender')==='other' ? 'selected' : ''); ?>><?php echo e(__('auth.gender_other')); ?></option>
                </select>
                <?php $__errorArgs = ['gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="field-label"><?php echo e(__('auth.orientation')); ?></label>
                <select name="orientation" required class="field-input">
                    <option value="">— <?php echo e(__('auth.choose')); ?> —</option>
                    <option value="hetero" <?php echo e(old('orientation')==='hetero' ? 'selected' : ''); ?>><?php echo e(__('auth.orientation_hetero')); ?></option>
                    <option value="homo" <?php echo e(old('orientation')==='homo' ? 'selected' : ''); ?>><?php echo e(__('auth.orientation_homo')); ?></option>
                    <option value="bi" <?php echo e(old('orientation')==='bi' ? 'selected' : ''); ?>><?php echo e(__('auth.orientation_bi')); ?></option>
                    <option value="other" <?php echo e(old('orientation')==='other' ? 'selected' : ''); ?>><?php echo e(__('auth.orientation_other')); ?></option>
                </select>
                <?php $__errorArgs = ['orientation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>
        <button type="submit" class="w-full bg-gradient-to-br from-purple-500 to-violet-700 text-white py-3 rounded-lg font-mono text-xs uppercase tracking-widest shadow-[0_0_20px_rgba(168,85,247,.3)] hover:shadow-[0_0_35px_rgba(168,85,247,.55)] hover:-translate-y-0.5 transition-all">
            <?php echo e(__('auth.register')); ?>

        </button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\malin\Desktop\altermatch\resources\views\auth\register.blade.php ENDPATH**/ ?>