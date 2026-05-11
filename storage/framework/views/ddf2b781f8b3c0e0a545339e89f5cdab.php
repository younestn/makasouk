<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($product->name); ?> | <?php echo e(__('shop.brand.shop_name')); ?></title>
    <meta name="description" content="<?php echo e($product->short_description ?: \Illuminate\Support\Str::limit($product->description, 140)); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/css/theme/shop-product-card.css']); ?>
</head>
<body>
<?php
    $galleryImages = $product->gallery_image_urls;
    $activeImage = $galleryImages[0] ?? null;
    $ratingAverage = $product->reviews_avg_rating !== null ? round((float) $product->reviews_avg_rating, 1) : null;
    $reviewsCount = (int) ($product->reviews_count ?? 0);
    $specifications = $product->localizedSpecifications();
    $orderNowUrl = url('/app/customer/orders/create?productId='.$product->id);
    $productCategory = $product->category;
    $productCategoryName = $productCategory?->display_name;
    $productCategoryUrl = $productCategory ? route('shop.category', $productCategory->slug) : null;
?>

<header class="public-header">
    <div class="container public-header-inner">
        <a class="brand" href="<?php echo e(route('shop.index')); ?>">
            <span class="brand-mark">MK</span>
            <span><?php echo e(__('shop.brand.shop_name')); ?></span>
        </a>
        <nav class="public-nav" aria-label="<?php echo e(__('shop.nav.breadcrumb')); ?>">
            <a class="public-nav-link" href="<?php echo e(route('shop.index')); ?>"><?php echo e(__('shop.nav.shop')); ?></a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($productCategoryName && $productCategoryUrl): ?>
                <a class="public-nav-link" href="<?php echo e($productCategoryUrl); ?>"><?php echo e($productCategoryName); ?></a>
            <?php elseif($productCategoryName): ?>
                <span class="public-nav-link"><?php echo e($productCategoryName); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </nav>
    </div>
</header>

<main class="page">
    <section class="container page-section stack" style="gap: 2rem;">
        <div class="product-detail-layout card">
            <article class="product-detail-media">
                <div class="product-detail-main-frame">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeImage): ?>
                        <img
                            src="<?php echo e($activeImage); ?>"
                            alt="<?php echo e($product->name); ?>"
                            class="product-detail-main-image"
                            data-product-gallery-main
                        >
                    <?php else: ?>
                        <div class="product-detail-placeholder">
                            <?php echo e(\Illuminate\Support\Str::of($product->name)->substr(0, 2)->upper()); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($galleryImages) > 1): ?>
                    <div class="product-detail-thumbnails" aria-label="<?php echo e(__('shop.product.gallery_title')); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $galleryImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $galleryImageUrl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button
                                type="button"
                                class="product-detail-thumb <?php if($index === 0): ?> is-active <?php endif; ?>"
                                data-product-gallery-thumb
                                data-image="<?php echo e($galleryImageUrl); ?>"
                            >
                                <img
                                    src="<?php echo e($galleryImageUrl); ?>"
                                    alt="<?php echo e($product->name); ?> <?php echo e($index + 1); ?>"
                                >
                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </article>

            <article class="product-detail-summary stack">
                <p class="small">
                    <?php echo e(__('shop.product.category')); ?>:
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($productCategoryName && $productCategoryUrl): ?>
                        <a class="product-detail-category" href="<?php echo e($productCategoryUrl); ?>"><?php echo e($productCategoryName); ?></a>
                    <?php elseif($productCategoryName): ?>
                        <span class="product-detail-category"><?php echo e($productCategoryName); ?></span>
                    <?php else: ?>
                        <span class="product-detail-category">-</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </p>

                <h1 class="product-detail-title"><?php echo e($product->name); ?></h1>

                <div class="product-detail-price-wrap">
                    <div class="product-detail-price-row">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->sale_price): ?>
                            <strong class="product-detail-price"><?php echo e(__('shop.product.price_mad', ['price' => number_format((float) $product->sale_price, 2)])); ?></strong>
                            <span class="product-detail-price-old"><?php echo e(__('shop.product.price_mad', ['price' => number_format((float) $product->price, 2)])); ?></span>
                        <?php else: ?>
                            <strong class="product-detail-price"><?php echo e(__('shop.product.price_mad', ['price' => number_format((float) $product->price, 2)])); ?></strong>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="row" style="gap: 0.5rem;">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->is_featured): ?>
                            <span class="badge badge-info"><?php echo e(__('shop.product.featured')); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->is_best_seller): ?>
                            <span class="badge badge-success"><?php echo e(__('shop.product.best_seller')); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->stock <= 0): ?>
                            <span class="badge badge-danger"><?php echo e(__('shop.product.out_of_stock')); ?></span>
                        <?php else: ?>
                            <span class="badge badge-warning"><?php echo e(__('shop.product.in_stock_badge', ['count' => $product->stock])); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="product-detail-rating">
                    <div class="product-rating-stars" aria-hidden="true">
                        <span class="<?php if(($ratingAverage ?? 0) >= 1): ?> is-filled <?php endif; ?>">&#9733;</span>
                        <span class="<?php if(($ratingAverage ?? 0) >= 2): ?> is-filled <?php endif; ?>">&#9733;</span>
                        <span class="<?php if(($ratingAverage ?? 0) >= 3): ?> is-filled <?php endif; ?>">&#9733;</span>
                        <span class="<?php if(($ratingAverage ?? 0) >= 4): ?> is-filled <?php endif; ?>">&#9733;</span>
                        <span class="<?php if(($ratingAverage ?? 0) >= 5): ?> is-filled <?php endif; ?>">&#9733;</span>
                    </div>
                    <p class="small">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ratingAverage && $reviewsCount > 0): ?>
                            <?php echo e(__('shop.product.rating_label', ['rating' => $ratingAverage])); ?>

                            <span aria-hidden="true">&middot;</span>
                            <?php echo e(__('shop.product.rating_count', ['count' => $reviewsCount])); ?>

                        <?php else: ?>
                            <?php echo e(__('shop.product.rating_fallback')); ?>

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </p>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($specifications !== []): ?>
                    <section class="product-detail-spec-grid">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $specifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $specification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="product-spec-card">
                                <span class="product-spec-label"><?php echo e($specification['label']); ?></span>
                                <strong class="product-spec-value"><?php echo e($specification['value']); ?></strong>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </section>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->display_fabric_type || $product->display_fabric_country || $product->display_fabric_description || $product->fabric_image_url): ?>
                    <section class="product-detail-fabric card stack">
                        <div class="row" style="justify-content: space-between; align-items: center;">
                            <h2 class="title product-detail-section-title"><?php echo e(__('shop.product.fabric_title')); ?></h2>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->fabric_image_url): ?>
                                <a
                                    class="ui-btn ui-btn--sm"
                                    href="<?php echo e($product->fabric_image_url); ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <?php echo e(__('shop.product.view_fabric_image')); ?>

                                </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="grid grid-2">
                            <div class="product-spec-card">
                                <span class="product-spec-label"><?php echo e(__('shop.product.fabric_type')); ?></span>
                                <strong class="product-spec-value"><?php echo e($product->display_fabric_type ?: '-'); ?></strong>
                            </div>
                            <div class="product-spec-card">
                                <span class="product-spec-label"><?php echo e(__('shop.product.fabric_country')); ?></span>
                                <strong class="product-spec-value"><?php echo e($product->display_fabric_country ?: '-'); ?></strong>
                            </div>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->display_fabric_description): ?>
                            <p class="small"><?php echo e($product->display_fabric_description); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </section>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <section class="product-detail-section">
                    <h2 class="title product-detail-section-title"><?php echo e(__('shop.product.details_title')); ?></h2>
                    <p class="small product-detail-text"><?php echo e($product->short_description ?: __('shop.product.details_placeholder')); ?></p>
                </section>

                <section class="product-detail-section">
                    <h2 class="title product-detail-section-title"><?php echo e(__('shop.product.description_title')); ?></h2>
                    <p class="small product-detail-text"><?php echo e($product->description ?: __('shop.product.description_placeholder')); ?></p>
                </section>

                <div class="actions">
                    <a class="ui-btn ui-btn--primary" href="<?php echo e($orderNowUrl); ?>"><?php echo e(__('shop.actions.order_from_app')); ?></a>
                    <a class="ui-btn" href="<?php echo e(route('shop.index')); ?>"><?php echo e(__('shop.actions.back_to_shop')); ?></a>
                </div>
            </article>
        </div>

        <section class="stack">
            <div class="ui-section-header">
                <p class="ui-section-eyebrow"><?php echo e(__('shop.sections.similar_products_eyebrow')); ?></p>
                <h2 class="ui-section-title"><?php echo e(__('shop.sections.similar_products_title')); ?></h2>
                <p class="ui-section-description"><?php echo e(__('shop.sections.similar_products_description')); ?></p>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($similarProducts->isEmpty()): ?>
                <?php echo $__env->make('shop.partials.empty-state', [
                    'title' => __('shop.empty.no_similar_products_title'),
                    'message' => __('shop.empty.no_similar_products_message'),
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php else: ?>
                <div class="storefront-product-grid">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $similarProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $similarProduct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('shop.partials.product-card', ['product' => $similarProduct], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>
    </section>
</main>

<script>
    document.querySelectorAll('[data-product-gallery-thumb]').forEach((button) => {
        button.addEventListener('click', () => {
            const mainImage = document.querySelector('[data-product-gallery-main]');

            if (!mainImage) {
                return;
            }

            mainImage.src = button.dataset.image || mainImage.src;

            document.querySelectorAll('[data-product-gallery-thumb]').forEach((thumb) => {
                thumb.classList.remove('is-active');
            });

            button.classList.add('is-active');
        });
    });
</script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\makasouk\resources\views\shop\product-show.blade.php ENDPATH**/ ?>