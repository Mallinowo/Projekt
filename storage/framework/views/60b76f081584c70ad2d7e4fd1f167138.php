<?php $__env->startSection('content'); ?>
<div class="auth-card p-5 sm:p-7">
    <div class="text-center mb-6">
        <div class="auth-brand font-cinzel text-3xl font-bold text-purple-400 tracking-widest mb-2">ALTERMATCH</div>
        <p class="text-[#8f87b1] font-mono text-xs tracking-[.22em] uppercase"><?php echo e(__('app.tagline')); ?></p>
    </div>

    <div class="auth-tabs mb-6">
        <a href="<?php echo e(route('login')); ?>" class="auth-tab"><?php echo e(__('auth.login')); ?></a>
        <a href="<?php echo e(route('register')); ?>" class="auth-tab is-active"><?php echo e(__('auth.register')); ?></a>
    </div>

    <form method="POST" action="<?php echo e(route('register')); ?>" class="auth-form">
        <?php echo csrf_field(); ?>
        <div class="auth-grid-name-age">
            <div class="auth-field">
                <label class="field-label"><?php echo e(__('auth.username')); ?></label>
                <input type="text" name="name" value="<?php echo e(old('name')); ?>" required maxlength="30" class="field-input auth-input" placeholder="moonchild_x">
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="auth-field">
                <label class="field-label"><?php echo e(__('auth.age')); ?></label>
                <input type="number" name="age" value="<?php echo e(old('age')); ?>" min="18" max="99" required class="field-input auth-input" placeholder="18">
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

        <div class="auth-field">
            <label class="field-label"><?php echo e(__('auth.email')); ?></label>
            <input type="email" name="email" value="<?php echo e(old('email')); ?>" required class="field-input auth-input" placeholder="twoj@email.com">
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="auth-grid-two">
            <div class="auth-field">
                <label class="field-label"><?php echo e(__('auth.password')); ?></label>
                <input type="password" name="password" required minlength="6" class="field-input auth-input" placeholder="min. 6 znakow">
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="auth-field">
                <label class="field-label"><?php echo e(__('auth.password_confirm')); ?></label>
                <input type="password" name="password_confirmation" required class="field-input auth-input" placeholder="powtorz haslo">
            </div>
        </div>

        <div class="auth-grid-two">
            <div class="auth-field">
                <label class="field-label"><?php echo e(__('auth.city')); ?></label>
                <input type="text" name="city" value="<?php echo e(old('city')); ?>" required class="field-input auth-input" placeholder="Warszawa">
                <?php $__errorArgs = ['city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="field-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="auth-field">
                <label class="field-label"><?php echo e(__('auth.subculture')); ?></label>
                <select name="subculture" required class="field-input auth-input">
                    <option value="">-- <?php echo e(__('auth.choose')); ?> --</option>
                    <?php $__currentLoopData = ['emo'=>'Emo','scene'=>'Scene','goth'=>'Goth','punk'=>'Punk','metalhead'=>'Metalhead']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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

        <div class="auth-grid-two">
            <div class="auth-field">
                <label class="field-label"><?php echo e(__('auth.gender')); ?></label>
                <select name="gender" required class="field-input auth-input">
                    <option value="">-- <?php echo e(__('auth.choose')); ?> --</option>
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
            <div class="auth-field">
                <label class="field-label"><?php echo e(__('auth.orientation')); ?></label>
                <select name="orientation" required class="field-input auth-input">
                    <option value="">-- <?php echo e(__('auth.choose')); ?> --</option>
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

        <button type="submit" class="auth-submit">
            <?php echo e(__('auth.register')); ?>

        </button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/auth/register.blade.php ENDPATH**/ ?>