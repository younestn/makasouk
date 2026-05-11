<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(__('shop.meta.web_client_title')); ?></title>
    <meta name="robots" content="noindex,nofollow">
    <script>
        window.__MAKASOUK__ = {
            locale: <?php echo \Illuminate\Support\Js::from(app()->getLocale())->toHtml() ?>,
            direction: <?php echo \Illuminate\Support\Js::from(app()->getLocale() === 'ar' ? 'rtl' : 'ltr')->toHtml() ?>,
        };
    </script>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/main.js']); ?>
</head>
<body>
<div id="app"></div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\makasouk\resources\views\spa.blade.php ENDPATH**/ ?>