<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($page->localizedTitle()); ?> | <?php echo e(__('shop.brand.shop_name')); ?></title>
    <meta name="description" content="<?php echo e($page->localizedExcerpt() ?: $page->localizedTitle()); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
</head>
<body>
<header class="public-header">
    <div class="container public-header-inner">
        <a class="brand" href="<?php echo e(url('/')); ?>">
            <span class="brand-mark">MK</span>
            <span><?php echo e(__('shop.brand.shop_name')); ?></span>
        </a>
        <nav class="public-nav" aria-label="<?php echo e(__('shop.nav.breadcrumb')); ?>">
            <a class="public-nav-link" href="<?php echo e(url('/')); ?>"><?php echo e(__('shop.nav.home')); ?></a>
            <a class="public-nav-link public-nav-link--shop" href="<?php echo e(route('shop.index')); ?>"><?php echo e(__('shop.nav.shop')); ?></a>
            <a class="public-nav-link" href="<?php echo e(url('/contact')); ?>"><?php echo e(__('shop.footer.contact')); ?></a>
        </nav>
    </div>
</header>

<main class="page">
    <section class="container page-section">
        <article class="content-page-card">
            <p class="premium-eyebrow"><?php echo e(__('shop.content.legal_badge')); ?></p>
            <h1 class="hero-title"><?php echo e($page->localizedTitle()); ?></h1>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($page->localizedExcerpt()): ?>
                <p class="hero-subtitle"><?php echo e($page->localizedExcerpt()); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="content-page-body">
                <?php echo $page->localizedBody() ?: '<p>'.e(__('shop.content.empty_body')).'</p>'; ?>

            </div>
        </article>
    </section>
</main>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\makasouk\resources\views\content-page\show.blade.php ENDPATH**/ ?>