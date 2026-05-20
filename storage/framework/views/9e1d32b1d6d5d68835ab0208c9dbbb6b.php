<!DOCTYPE html>
<html><head><meta charset="UTF-8"><style>
body{background:#0a0a0f;color:#e2e0f0;font-family:serif;padding:2rem;}
.box{max-width:480px;margin:auto;background:#111118;border:1px solid #2a2a3a;border-radius:16px;padding:2rem;}
.logo{font-size:1.4rem;font-weight:bold;color:#a855f7;letter-spacing:.2em;margin-bottom:1rem;}
h1{color:#a855f7;font-size:1.2rem;}
p{color:#9991bb;line-height:1.7;}
.btn{display:inline-block;background:linear-gradient(135deg,#a855f7,#7c3aed);color:#fff;padding:.7rem 1.5rem;border-radius:8px;text-decoration:none;font-size:.85rem;margin-top:1rem;}
</style></head><body>
<div class="box">
    <div class="logo">✦ ALTERMATCH</div>
    <h1><?php echo e(__('mail.welcome_heading', ['name' => $user->name])); ?></h1>
    <p><?php echo e(__('mail.welcome_body')); ?></p>
    <a href="<?php echo e(config('app.url')); ?>/discover" class="btn"><?php echo e(__('mail.welcome_cta')); ?></a>
    <p style="margin-top:1.5rem;font-size:.8rem;color:#5e5880;"><?php echo e(__('mail.footer')); ?></p>
</div>
</body></html>
<?php /**PATH /var/www/resources/views/emails/welcome.blade.php ENDPATH**/ ?>