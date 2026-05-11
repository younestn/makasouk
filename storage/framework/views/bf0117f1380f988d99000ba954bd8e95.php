<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banners->isEmpty()): ?>
    <?php echo $__env->make('shop.partials.empty-state', [
        'title' => __('shop.empty.no_hero_title'),
        'message' => __('shop.empty.no_hero_message'),
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php else: ?>
    <section class="shop-hero" data-shop-hero data-autoplay="<?php echo e($settings->hero_autoplay ? '1' : '0'); ?>" data-delay="<?php echo e($settings->hero_autoplay_delay_ms); ?>">
        <div class="shop-hero-track" data-shop-hero-track>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $banners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $banner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="shop-hero-slide <?php echo e($index === 0 ? 'is-active' : ''); ?>" data-shop-hero-slide>
                    <div class="shop-hero-media">
                        <?php
                            $bannerImage = \Illuminate\Support\Str::startsWith($banner->image_path, ['http://', 'https://', '/'])
                                ? $banner->image_path
                                : \Illuminate\Support\Facades\Storage::url($banner->image_path);
                        ?>
                        <img src="<?php echo e($bannerImage); ?>" alt="<?php echo e($banner->title); ?>" loading="lazy">
                    </div>
                    <div class="shop-hero-content">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banner->badge): ?>
                            <span class="badge badge-warning"><?php echo e($banner->badge); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <h1><?php echo e($banner->title); ?></h1>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banner->subtitle): ?>
                            <p><?php echo e($banner->subtitle); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($banner->button_link): ?>
                            <a class="ui-btn ui-btn--primary" href="<?php echo e($banner->button_link); ?>"><?php echo e($banner->button_text ?: __('shop.actions.explore')); ?></a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <button type="button" class="shop-hero-nav shop-hero-prev" data-shop-hero-prev aria-label="<?php echo e(__('shop.pagination.previous')); ?>">&#8249;</button>
        <button type="button" class="shop-hero-nav shop-hero-next" data-shop-hero-next aria-label="<?php echo e(__('shop.pagination.next')); ?>">&#8250;</button>

        <div class="shop-hero-dots">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $banners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $banner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button type="button" class="shop-hero-dot <?php echo e($index === 0 ? 'is-active' : ''); ?>" data-shop-hero-dot="<?php echo e($index); ?>" aria-label="<?php echo e(__('shop.actions.go_to_banner', ['index' => $index + 1])); ?>"></button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php /**PATH C:\xampp\htdocs\makasouk\resources\views/shop/partials/hero-slider.blade.php ENDPATH**/ ?>