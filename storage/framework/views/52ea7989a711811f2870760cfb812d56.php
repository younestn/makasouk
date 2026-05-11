<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(__('shop.meta.title')); ?></title>
    <meta name="description" content="<?php echo e(__('shop.meta.description')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/css/theme/shop-product-card.css']); ?>
</head>
<body>
<header class="public-header">
    <div class="container public-header-inner">
        <a class="brand" href="<?php echo e(url('/')); ?>">
            <span class="brand-mark">MK</span>
            <span><?php echo e(__('shop.brand.shop_name')); ?></span>
        </a>

        <nav class="public-nav" aria-label="<?php echo e(__('shop.nav.shop_navigation')); ?>">
            <a class="public-nav-link" href="<?php echo e(url('/')); ?>"><?php echo e(__('shop.nav.home')); ?></a>
            <a class="public-nav-link" href="<?php echo e(route('shop.index')); ?>"><?php echo e(__('shop.nav.shop')); ?></a>
            <a class="public-nav-link" href="<?php echo e(url('/app/login')); ?>"><?php echo e(__('shop.nav.login')); ?></a>
            <a class="public-nav-link" href="<?php echo e(url('/admin-panel/login')); ?>"><?php echo e(__('shop.nav.admin')); ?></a>
            <a class="public-nav-link" href="<?php echo e(route('locale.switch', ['locale' => app()->getLocale() === 'ar' ? 'en' : 'ar'])); ?>">
                <?php echo e(app()->getLocale() === 'ar' ? __('shop.nav.english') : __('shop.nav.arabic')); ?>

            </a>
        </nav>
    </div>
</header>

<main class="page">
    <section class="container stack" style="gap: 2rem;">
        <?php
            $sectionOrder = $settings->section_order ?? ['hero', 'categories', 'new_arrivals', 'best_sellers', 'category_sections', 'all_products'];
        ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sectionOrder; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($section):
                case ('hero'): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($settings->hero_enabled): ?>
                        <?php echo $__env->make('shop.partials.hero-slider', ['banners' => $banners, 'settings' => $settings], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php break; ?>

                <?php case ('categories'): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($settings->category_blocks_enabled): ?>
                        <section class="page-section">
                            <?php echo $__env->make('shop.partials.section-header', [
                                'eyebrow' => __('shop.sections.browse_eyebrow'),
                                'title' => __('shop.sections.browse_title'),
                                'description' => __('shop.sections.browse_description'),
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                            <?php echo $__env->make('shop.partials.category-blocks', ['categories' => $categories], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </section>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php break; ?>

                <?php case ('new_arrivals'): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($settings->new_arrivals_enabled): ?>
                        <section class="page-section">
                            <?php echo $__env->make('shop.partials.section-header', [
                                'eyebrow' => __('shop.sections.new_arrivals_eyebrow'),
                                'title' => __('shop.sections.new_arrivals_title'),
                                'description' => __('shop.sections.new_arrivals_description'),
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($newArrivals->isEmpty()): ?>
                                <?php echo $__env->make('shop.partials.empty-state', [
                                    'title' => __('shop.empty.no_new_arrivals_title'),
                                    'message' => __('shop.empty.no_new_arrivals_message'),
                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php else: ?>
                                <div class="storefront-product-grid">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $newArrivals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php echo $__env->make('shop.partials.product-card', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </section>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php break; ?>

                <?php case ('best_sellers'): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($settings->best_sellers_enabled): ?>
                        <section class="page-section">
                            <?php echo $__env->make('shop.partials.section-header', [
                                'eyebrow' => __('shop.sections.best_sellers_eyebrow'),
                                'title' => __('shop.sections.best_sellers_title'),
                                'description' => __('shop.sections.best_sellers_description'),
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bestSellers->isEmpty()): ?>
                                <?php echo $__env->make('shop.partials.empty-state', [
                                    'title' => __('shop.empty.no_best_sellers_title'),
                                    'message' => __('shop.empty.no_best_sellers_message'),
                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php else: ?>
                                <div class="storefront-product-grid">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bestSellers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php echo $__env->make('shop.partials.product-card', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </section>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php break; ?>

                <?php case ('category_sections'): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($settings->category_sections_enabled): ?>
                        <section class="page-section">
                            <?php echo $__env->make('shop.partials.section-header', [
                                'eyebrow' => __('shop.sections.featured_categories_eyebrow'),
                                'title' => __('shop.sections.featured_categories_title'),
                                'description' => __('shop.sections.featured_categories_description'),
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($categorySections->isEmpty()): ?>
                                <?php echo $__env->make('shop.partials.empty-state', [
                                    'title' => __('shop.empty.no_featured_categories_title'),
                                    'message' => __('shop.empty.no_featured_categories_message'),
                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php else: ?>
                                <div class="stack" style="gap:2rem;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categorySections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <section class="stack">
                                            <div class="row" style="justify-content: space-between; align-items: end;">
                                                <div>
                                                    <h3 class="title"><?php echo e($category->display_name); ?></h3>
                                                    <p class="small"><?php echo e($category->display_description ?: __('shop.sections.category_fallback_description')); ?></p>
                                                </div>
                                                <a class="ui-btn ui-btn--secondary ui-btn--sm" href="<?php echo e(route('shop.category', $category->slug)); ?>"><?php echo e(__('shop.actions.view_more')); ?></a>
                                            </div>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($category->products->isEmpty()): ?>
                                                <?php echo $__env->make('shop.partials.empty-state', [
                                                    'title' => __('shop.empty.no_products_in_category_title'),
                                                    'message' => __('shop.empty.no_products_in_category_message'),
                                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            <?php else: ?>
                                                <div class="storefront-product-grid">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $category->products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php echo $__env->make('shop.partials.product-card', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </section>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </section>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php break; ?>

                <?php case ('all_products'): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($settings->all_products_enabled): ?>
                        <section class="page-section" id="all-products">
                            <?php echo $__env->make('shop.partials.section-header', [
                                'eyebrow' => __('shop.sections.all_products_eyebrow'),
                                'title' => __('shop.sections.all_products_title'),
                                'description' => __('shop.sections.all_products_description'),
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                            <form class="shop-filters card" action="<?php echo e(route('shop.index')); ?>" method="GET">
                                <div class="grid grid-4">
                                    <div>
                                        <label class="label" for="q"><?php echo e(__('shop.filters.search')); ?></label>
                                        <input class="input" id="q" type="text" name="q" value="<?php echo e($filters['q'] ?? ''); ?>" placeholder="<?php echo e(__('shop.filters.search_placeholder')); ?>">
                                    </div>

                                    <div>
                                        <label class="label" for="category"><?php echo e(__('shop.filters.category')); ?></label>
                                        <select class="select" id="category" name="category">
                                            <option value=""><?php echo e(__('shop.filters.all_categories')); ?></option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($category->slug); ?>" <?php if(($filters['category'] ?? '') === $category->slug): echo 'selected'; endif; ?>>
                                                    <?php echo e($category->display_name); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="label" for="sort"><?php echo e(__('shop.filters.sort_by')); ?></label>
                                        <select class="select" id="sort" name="sort">
                                            <option value="newest" <?php if(($filters['sort'] ?? 'newest') === 'newest'): echo 'selected'; endif; ?>><?php echo e(__('shop.filters.newest')); ?></option>
                                            <option value="price_asc" <?php if(($filters['sort'] ?? '') === 'price_asc'): echo 'selected'; endif; ?>><?php echo e(__('shop.filters.price_asc')); ?></option>
                                            <option value="price_desc" <?php if(($filters['sort'] ?? '') === 'price_desc'): echo 'selected'; endif; ?>><?php echo e(__('shop.filters.price_desc')); ?></option>
                                            <option value="best_selling" <?php if(($filters['sort'] ?? '') === 'best_selling'): echo 'selected'; endif; ?>><?php echo e(__('shop.filters.best_selling')); ?></option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="label" for="min_price"><?php echo e(__('shop.filters.min_price')); ?></label>
                                        <input class="input" id="min_price" type="number" step="0.01" min="0" name="min_price" value="<?php echo e($filters['min_price'] ?? ''); ?>">
                                    </div>

                                    <div>
                                        <label class="label" for="max_price"><?php echo e(__('shop.filters.max_price')); ?></label>
                                        <input class="input" id="max_price" type="number" step="0.01" min="0" name="max_price" value="<?php echo e($filters['max_price'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="row" style="justify-content: space-between; margin-top: 1rem;">
                                    <div class="row">
                                        <label class="row"><input type="checkbox" name="featured" value="1" <?php if(($filters['featured'] ?? null) == 1): echo 'checked'; endif; ?>> <?php echo e(__('shop.filters.featured')); ?></label>
                                        <label class="row"><input type="checkbox" name="best_seller" value="1" <?php if(($filters['best_seller'] ?? null) == 1): echo 'checked'; endif; ?>> <?php echo e(__('shop.filters.best_seller')); ?></label>
                                        <label class="row"><input type="checkbox" name="in_stock" value="1" <?php if(($filters['in_stock'] ?? null) == 1): echo 'checked'; endif; ?>> <?php echo e(__('shop.filters.in_stock')); ?></label>
                                    </div>
                                    <div class="row">
                                        <a class="ui-btn ui-btn--ghost" href="<?php echo e(route('shop.index')); ?>#all-products"><?php echo e(__('shop.actions.reset')); ?></a>
                                        <button class="ui-btn ui-btn--primary" type="submit"><?php echo e(__('shop.actions.apply_filters')); ?></button>
                                    </div>
                                </div>
                            </form>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allProducts->isEmpty()): ?>
                                <?php echo $__env->make('shop.partials.empty-state', [
                                    'title' => __('shop.empty.no_products_match_title'),
                                    'message' => __('shop.empty.no_products_match_message'),
                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php else: ?>
                                <div class="storefront-product-grid">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $allProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php echo $__env->make('shop.partials.product-card', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <div class="ui-pagination" style="margin-top: 1.2rem;">
                                    <div class="small">
                                        <?php echo e(__('shop.pagination.showing', ['from' => $allProducts->firstItem(), 'to' => $allProducts->lastItem(), 'total' => $allProducts->total()])); ?>

                                    </div>
                                    <div class="row">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allProducts->onFirstPage()): ?>
                                            <span class="ui-btn ui-btn--disabled ui-btn--sm"><?php echo e(__('shop.pagination.previous')); ?></span>
                                        <?php else: ?>
                                            <a class="ui-btn ui-btn--sm" href="<?php echo e($allProducts->previousPageUrl()); ?>"><?php echo e(__('shop.pagination.previous')); ?></a>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                        <span class="small"><?php echo e(__('shop.pagination.page', ['current' => $allProducts->currentPage(), 'last' => $allProducts->lastPage()])); ?></span>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allProducts->hasMorePages()): ?>
                                            <a class="ui-btn ui-btn--sm" href="<?php echo e($allProducts->nextPageUrl()); ?>"><?php echo e(__('shop.pagination.next')); ?></a>
                                        <?php else: ?>
                                            <span class="ui-btn ui-btn--disabled ui-btn--sm"><?php echo e(__('shop.pagination.next')); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </section>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php break; ?>
            <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </section>
</main>

<footer class="public-footer">
    <div class="container public-footer-inner">
        <p class="small"><?php echo e(__('shop.footer.copyright', ['year' => now()->year])); ?></p>
        <div class="row">
            <a class="public-footer-link" href="<?php echo e(route('shop.index')); ?>"><?php echo e(__('shop.nav.shop')); ?></a>
            <a class="public-footer-link" href="<?php echo e(url('/contact')); ?>"><?php echo e(__('shop.footer.contact')); ?></a>
        </div>
    </div>
</footer>

<script>
    document.querySelectorAll('[data-shop-hero]').forEach((hero) => {
        const slides = Array.from(hero.querySelectorAll('[data-shop-hero-slide]'));
        const dots = Array.from(hero.querySelectorAll('[data-shop-hero-dot]'));
        const prev = hero.querySelector('[data-shop-hero-prev]');
        const next = hero.querySelector('[data-shop-hero-next]');

        if (!slides.length) {
            return;
        }

        let activeIndex = 0;
        let timer = null;
        const autoplayEnabled = hero.dataset.autoplay === '1';
        const autoplayDelay = Number(hero.dataset.delay || 6000);

        const render = (index) => {
            activeIndex = (index + slides.length) % slides.length;

            slides.forEach((slide, slideIndex) => {
                slide.classList.toggle('is-active', slideIndex === activeIndex);
            });

            dots.forEach((dot, dotIndex) => {
                dot.classList.toggle('is-active', dotIndex === activeIndex);
            });
        };

        const startAutoplay = () => {
            if (!autoplayEnabled || slides.length < 2) {
                return;
            }

            clearInterval(timer);
            timer = setInterval(() => render(activeIndex + 1), autoplayDelay);
        };

        prev?.addEventListener('click', () => {
            render(activeIndex - 1);
            startAutoplay();
        });

        next?.addEventListener('click', () => {
            render(activeIndex + 1);
            startAutoplay();
        });

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                render(index);
                startAutoplay();
            });
        });

        render(0);
        startAutoplay();
    });
</script>
</body>
</html>

<?php /**PATH C:\xampp\htdocs\makasouk\resources\views\shop\index.blade.php ENDPATH**/ ?>