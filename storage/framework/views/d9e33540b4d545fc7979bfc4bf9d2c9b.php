<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('app.name')); ?> | Public Site</title>
    <meta name="description" content="Makasouk connects customers and tailors through realtime order workflows and lifecycle clarity.">
    <meta name="robots" content="index,follow">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo e(config('app.name')); ?> | Public Site">
    <meta property="og:description" content="Makasouk connects customers and tailors through realtime order workflows and lifecycle clarity.">
    <meta property="og:url" content="<?php echo e(url()->current()); ?>">
    <meta property="og:site_name" content="<?php echo e(config('app.name')); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo e(config('app.name')); ?> | Public Site">
    <meta name="twitter:description" content="Makasouk connects customers and tailors through realtime order workflows and lifecycle clarity.">
    <link rel="canonical" href="<?php echo e(url()->current()); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/public/main.js']); ?>
</head>
<body>
<div id="public-site"></div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\makasouk\resources\views/public-site.blade.php ENDPATH**/ ?>