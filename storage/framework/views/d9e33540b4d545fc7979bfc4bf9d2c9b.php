<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('app.name')); ?> | Public Site</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/public/main.js']); ?>
</head>
<body>
<div id="public-site"></div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\makasouk\resources\views/public-site.blade.php ENDPATH**/ ?>