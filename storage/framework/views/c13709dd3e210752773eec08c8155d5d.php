<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php if (! empty(trim($__env->yieldContent('title')))): ?><?php echo $__env->yieldContent('title'); ?> | <?php endif; ?><?php echo e(config('app.name')); ?></title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('img/ikonaa.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('img/ikonaa.png')); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Pro:wght@300;400&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        input.field-input,
        textarea.field-input,
        select.field-input {
            background-color: #16161f !important;
            color: #e2e0f0 !important;
        }
        input.field-input:-webkit-autofill,
        input.field-input:-webkit-autofill:hover,
        input.field-input:-webkit-autofill:focus,
        textarea.field-input:-webkit-autofill,
        textarea.field-input:-webkit-autofill:hover,
        textarea.field-input:-webkit-autofill:focus,
        select.field-input:-webkit-autofill,
        select.field-input:-webkit-autofill:hover,
        select.field-input:-webkit-autofill:focus {
            -webkit-text-fill-color: #e2e0f0 !important;
            caret-color: #e2e0f0 !important;
            -webkit-box-shadow: 0 0 0 1000px #16161f inset !important;
            box-shadow: 0 0 0 1000px #16161f inset !important;
            background-color: #16161f !important;
            border-color: #2a2a3a !important;
        }
    </style>
</head>
<body class="bg-[#0a0a0f] text-[#e2e0f0] font-crimson h-full overflow-hidden">


<nav class="flex items-center justify-between px-4 md:px-6 h-16 bg-[#111118] border-b border-[#2a2a3a] z-50 relative flex-shrink-0">
    <a href="<?php echo e(route('discover')); ?>" class="font-cinzel text-lg font-bold tracking-widest text-purple-400 hover:text-[#d6b6ff] transition-colors" style="text-shadow:0 0 18px rgba(168,85,247,.5)">
        ✦ ALTERMATCH
    </a>
    <div class="flex gap-1 items-center">
        <a href="<?php echo e(route('discover')); ?>" class="nav-btn <?php echo e(request()->routeIs('discover','home') ? 'active' : ''); ?>"><?php echo e(__('nav.discover')); ?></a>
        <a href="<?php echo e(route('chat')); ?>"     class="nav-btn <?php echo e(request()->routeIs('chat*') ? 'active' : ''); ?> relative">
            <?php echo e(__('nav.chat')); ?>

            <?php if(isset($unreadCount) && $unreadCount > 0): ?>
                <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-pink-500 shadow-[0_0_6px_theme(colors.pink.500)]"></span>
            <?php endif; ?>
        </a>
        <a href="<?php echo e(route('profile')); ?>"  class="nav-btn <?php echo e(request()->routeIs('profile*') ? 'active' : ''); ?>"><?php echo e(__('nav.profile')); ?></a>
        <div class="w-px h-6 bg-[#2a2a3a] mx-1"></div>

        
        <a href="<?php echo e(route('profile')); ?>" class="w-10 h-10 rounded-full border-2 border-[#2a2a3a] overflow-hidden flex items-center justify-center bg-gradient-to-br from-purple-900 to-blue-900 hover:border-purple-400 transition-colors">
            <?php if(auth()->user()->getAvatarUrl()): ?>
                <img src="<?php echo e(auth()->user()->getAvatarUrl()); ?>" class="w-full h-full object-cover">
            <?php else: ?>
                <span class="text-xs font-bold"><?php echo e(strtoupper(substr(auth()->user()->name,0,1))); ?></span>
            <?php endif; ?>
        </a>

        
        <div class="flex gap-1 ml-1">
            <a href="<?php echo e(route('locale','pl')); ?>" class="lang-btn <?php echo e(app()->getLocale()==='pl' ? 'active' : ''); ?>">PL</a>
            <a href="<?php echo e(route('locale','en')); ?>" class="lang-btn <?php echo e(app()->getLocale()==='en' ? 'active' : ''); ?>">EN</a>
        </div>

        <form method="POST" action="<?php echo e(route('logout')); ?>" class="ml-1">
            <?php echo csrf_field(); ?>
            <button class="font-mono text-[.66rem] text-[#5e5880] border border-[#2a2a3a] px-2.5 py-1 rounded hover:border-red-500 hover:text-red-500 transition-all uppercase tracking-wider">
                <?php echo e(__('auth.logout')); ?>

            </button>
        </form>
    </div>
</nav>

<div class="flex-1 overflow-hidden">
    <?php echo $__env->yieldContent('content'); ?>
</div>


<div id="toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 translate-y-20 bg-[#111118] border border-[#2a2a3a] text-[#e2e0f0] px-5 py-2 rounded-full font-mono text-xs tracking-wide transition-transform duration-300 z-[700] whitespace-nowrap shadow-2xl opacity-0" id="toast"></div>

<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\malin\Desktop\altermatch\resources\views\layouts\app.blade.php ENDPATH**/ ?>