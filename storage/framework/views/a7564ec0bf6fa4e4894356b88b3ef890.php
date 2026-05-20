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
    <style>
        .auth-page {
            min-height: 100vh;
            background:
                radial-gradient(circle at 18% 8%, rgba(168, 85, 247, .18), transparent 34%),
                radial-gradient(circle at 82% 26%, rgba(34, 211, 238, .09), transparent 32%),
                linear-gradient(180deg, #0c0b13 0%, #08080d 100%);
        }
        .auth-shell {
            width: min(100% - 32px, 448px);
            margin: 0 auto;
            padding: 24px 0;
        }
        .auth-shell.is-register {
            width: min(100% - 32px, 760px);
        }
        .auth-lang {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-bottom: 16px;
        }
        .auth-card {
            position: relative;
            overflow: hidden;
            border: 1px solid #2f2944;
            border-radius: 20px;
            background:
                linear-gradient(165deg, rgba(17, 17, 24, .98), rgba(12, 12, 18, .98)),
                radial-gradient(circle at 12% -20%, rgba(168, 85, 247, .18), transparent 42%);
            box-shadow: 0 22px 60px rgba(0, 0, 0, .45), inset 0 1px 0 rgba(255, 255, 255, .04);
        }
        .auth-card::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            border-radius: inherit;
            background: linear-gradient(120deg, rgba(168, 85, 247, .26), transparent 32%, rgba(236, 72, 153, .12));
            opacity: .42;
        }
        .auth-card > * {
            position: relative;
            z-index: 1;
        }
        .auth-brand {
            text-shadow: 0 0 28px rgba(168, 85, 247, .42);
        }
        .auth-tabs {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 4px;
            border: 1px solid #2a2a3a;
            border-radius: 12px;
            padding: 4px;
            background: rgba(10, 10, 15, .55);
        }
        .auth-tab {
            border-radius: 9px;
            padding: 10px 12px;
            text-align: center;
            font-family: Space Mono, monospace;
            font-size: .68rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #81799f;
            transition: color .18s ease, background-color .18s ease;
        }
        .auth-tab:hover {
            color: #c084fc;
        }
        .auth-tab.is-active {
            color: #fff;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            box-shadow: 0 8px 18px rgba(168, 85, 247, .2);
        }
        .auth-field {
            min-width: 0;
        }
        .auth-form {
            display: grid;
            gap: 20px;
        }
        .auth-grid-name-age,
        .auth-grid-two {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }
        .auth-input {
            min-height: 44px;
            border-color: #312b43;
            background: rgba(22, 22, 31, .96);
            font-size: 1rem;
        }
        .auth-input:focus {
            border-color: #a855f7;
            box-shadow: 0 0 0 3px rgba(168, 85, 247, .12);
        }
        .auth-submit {
            width: 100%;
            min-height: 46px;
            border-radius: 12px;
            background: linear-gradient(135deg, #a855f7, #6d28d9);
            color: #fff;
            font-family: Space Mono, monospace;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            box-shadow: 0 12px 24px rgba(168, 85, 247, .24);
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .auth-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 34px rgba(168, 85, 247, .34);
        }
        .auth-footer {
            margin-top: 20px;
            text-align: center;
            color: #7f79a6;
            font-size: .82rem;
        }
        .auth-footer a {
            color: #d8b4fe;
        }
        .auth-footer a:hover {
            color: #f3e8ff;
        }
        @media (min-width: 640px) {
            .auth-shell {
                padding: 40px 0;
            }
            .auth-grid-name-age {
                grid-template-columns: minmax(0, 1fr) 128px;
            }
        }
        @media (min-width: 768px) {
            .auth-grid-two {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
</head>
<body class="auth-page text-[#e2e0f0] font-crimson overflow-y-auto">
    <div class="auth-shell <?php echo e(request()->routeIs('register') ? 'is-register' : ''); ?>">
        <div class="auth-lang">
            <a href="<?php echo e(route('locale','pl')); ?>" class="lang-btn <?php echo e(app()->getLocale()==='pl'?'active':''); ?>">PL</a>
            <a href="<?php echo e(route('locale','en')); ?>" class="lang-btn <?php echo e(app()->getLocale()==='en'?'active':''); ?>">EN</a>
        </div>
        <?php echo $__env->yieldContent('content'); ?>
    </div>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /var/www/resources/views/layouts/auth.blade.php ENDPATH**/ ?>