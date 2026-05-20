<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php if (! empty(trim($__env->yieldContent('title')))): ?><?php echo $__env->yieldContent('title'); ?> | <?php endif; ?><?php echo e(config('app.name')); ?></title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('img/ikonaa.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('img/ikonaa.png')); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Pro:wght@300;400&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="bg-[#0a0a0f] text-[#e2e0f0] font-crimson h-full flex items-center justify-center" style="background-image:radial-gradient(ellipse at 50% 30%,rgba(168,85,247,.12),transparent 70%)">
    <div class="w-full max-w-sm px-4">
        
        <div class="flex justify-end gap-2 mb-4">
            <a href="<?php echo e(route('locale','pl')); ?>" class="lang-btn <?php echo e(app()->getLocale()==='pl'?'active':''); ?>">PL</a>
            <a href="<?php echo e(route('locale','en')); ?>" class="lang-btn <?php echo e(app()->getLocale()==='en'?'active':''); ?>">EN</a>
        </div>
        <?php echo $__env->yieldContent('content'); ?>
    </div>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\malin\Desktop\altermatch\resources\views\layouts\auth.blade.php ENDPATH**/ ?>