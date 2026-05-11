<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(__('shop.meta.public_title')); ?></title>
    <meta name="description" content="<?php echo e(__('shop.meta.public_description')); ?>">
    <meta name="robots" content="index,follow">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo e(__('shop.meta.public_title')); ?>">
    <meta property="og:description" content="<?php echo e(__('shop.meta.public_description')); ?>">
    <meta property="og:url" content="<?php echo e(url()->current()); ?>">
    <meta property="og:site_name" content="<?php echo e(config('app.name')); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo e(__('shop.meta.public_title')); ?>">
    <meta name="twitter:description" content="<?php echo e(__('shop.meta.public_description')); ?>">
    <link rel="canonical" href="<?php echo e(url()->current()); ?>">
    <script>
        window.__MAKASOUK__ = {
            locale: <?php echo \Illuminate\Support\Js::from(app()->getLocale())->toHtml() ?>,
            direction: <?php echo \Illuminate\Support\Js::from(app()->getLocale() === 'ar' ? 'rtl' : 'ltr')->toHtml() ?>,
        };
    </script>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/public/main.js']); ?>
</head>
<body>
<div id="public-site"></div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\makasouk\resources\views/public-site.blade.php ENDPATH**/ ?>