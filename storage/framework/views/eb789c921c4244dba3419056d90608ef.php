<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['product']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['product']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $productUrl = route('shop.product.show', $product->slug);
    $productImage = $product->main_image_url;
    $category = $product->category;
    $categoryName = $category?->display_name;
    $categoryUrl = $category ? route('shop.category', $category->slug) : null;
    $stockCount = (int) ($product->stock ?? 0);
    $isOutOfStock = $stockCount <= 0;
    $isNew = $product->published_at?->greaterThan(now()->subDays(14)) ?? false;
    $hasSalePrice = filled($product->sale_price) && (float) $product->sale_price < (float) $product->price;
    $currentPrice = $hasSalePrice ? (float) $product->sale_price : (float) $product->price;
    $originalPrice = (float) $product->price;
    $discountPercent = $hasSalePrice && $originalPrice > 0
        ? (int) round((($originalPrice - $currentPrice) / $originalPrice) * 100)
        : null;
    $ratingAverage = $product->reviews_avg_rating !== null ? round((float) $product->reviews_avg_rating, 1) : null;
    $reviewsCount = (int) ($product->reviews_count ?? 0);
    $ratingFill = $ratingAverage !== null ? max(0, min(100, ($ratingAverage / 5) * 100)) : 0;
    $summary = trim((string) ($product->short_description ?: $product->description ?: ''));
    $summary = $summary !== '' ? \Illuminate\Support\Str::limit($summary, 96) : __('shop.product.tailoring_placeholder');
    $placeholderParts = collect(preg_split('/\s+/u', trim((string) $product->name)) ?: [])
        ->filter()
        ->take(2)
        ->map(fn (string $segment): string => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($segment, 0, 1)));
    $placeholder = $placeholderParts->implode('');
    $placeholder = $placeholder !== ''
        ? $placeholder
        : \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr((string) $product->name, 0, 2));
?>

<article class="shop-product-card">
    <a
        class="shop-product-media"
        href="<?php echo e($productUrl); ?>"
        aria-label="<?php echo e(__('shop.actions.view_product')); ?>: <?php echo e($product->name); ?>"
    >
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($productImage): ?>
            <img
                src="<?php echo e($productImage); ?>"
                alt="<?php echo e($product->name); ?>"
                loading="lazy"
                decoding="async"
            >
        <?php else: ?>
            <div class="shop-product-placeholder" aria-hidden="true"><?php echo e($placeholder); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="shop-product-badges">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($discountPercent): ?>
                <span class="badge badge-danger text-bg-danger shop-product-badge shop-product-badge--discount">-<?php echo e($discountPercent); ?>%</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->is_featured): ?>
                <span class="badge badge-info text-bg-info shop-product-badge"><?php echo e(__('shop.product.featured')); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->is_best_seller): ?>
                <span class="badge badge-success text-bg-success shop-product-badge"><?php echo e(__('shop.product.best_seller')); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isNew): ?>
                <span class="badge badge-warning text-bg-warning shop-product-badge"><?php echo e(__('shop.product.new')); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isOutOfStock): ?>
                <span class="badge badge-danger text-bg-danger shop-product-badge"><?php echo e(__('shop.product.out_of_stock')); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </a>

    <div class="shop-product-card__body">
        <div class="shop-product-card__header">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($categoryName && $categoryUrl): ?>
                <a class="shop-product-category" href="<?php echo e($categoryUrl); ?>"><?php echo e($categoryName); ?></a>
            <?php elseif($categoryName): ?>
                <span class="shop-product-category"><?php echo e($categoryName); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div
                class="shop-product-rating"
                <?php if($ratingAverage !== null && $reviewsCount > 0): ?>
                    aria-label="<?php echo e(__('shop.product.rating_label', ['rating' => $ratingAverage])); ?>"
                <?php endif; ?>
            >
                <span class="shop-product-rating__stars" aria-hidden="true">
                    <span class="shop-product-rating__stars-base">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                    <span class="shop-product-rating__stars-fill" style="width: <?php echo e($ratingFill); ?>%;">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                </span>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ratingAverage !== null && $reviewsCount > 0): ?>
                    <span class="shop-product-rating__value"><?php echo e($ratingAverage); ?></span>
                    <span class="shop-product-rating__count">(<?php echo e($reviewsCount); ?>)</span>
                <?php else: ?>
                    <span class="shop-product-rating__empty"><?php echo e(__('shop.product.rating_fallback')); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="shop-product-card__content">
            <a class="shop-product-name" href="<?php echo e($productUrl); ?>" title="<?php echo e($product->name); ?>"><?php echo e($product->name); ?></a>
            <p class="small shop-product-summary"><?php echo e($summary); ?></p>
        </div>

        <div class="shop-product-price-row">
            <div class="shop-product-price-block">
                <strong class="shop-product-price-current"><?php echo e(__('shop.product.price_mad', ['price' => number_format($currentPrice, 2)])); ?></strong>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasSalePrice): ?>
                    <span class="small shop-product-price-old"><?php echo e(__('shop.product.price_mad', ['price' => number_format($originalPrice, 2)])); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <span class="badge <?php echo e($isOutOfStock ? 'badge-danger text-bg-danger' : 'badge-neutral text-bg-secondary'); ?> shop-product-stock">
                <?php echo e($isOutOfStock ? __('shop.product.out_of_stock') : __('shop.product.in_stock_badge', ['count' => $stockCount])); ?>

            </span>
        </div>

        <div class="shop-product-card__actions">
            <a class="ui-btn ui-btn--primary ui-btn--sm ui-btn--block" href="<?php echo e($productUrl); ?>">
                <?php echo e(__('shop.product.view_product')); ?>

            </a>
        </div>
    </div>
</article>
<?php /**PATH C:\xampp\htdocs\makasouk\resources\views\components\product-card.blade.php ENDPATH**/ ?>