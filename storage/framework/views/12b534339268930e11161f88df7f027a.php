<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($categories->isEmpty()): ?>
    <?php echo $__env->make('shop.partials.empty-state', [
        'title' => __('shop.empty.no_categories_title'),
        'message' => __('shop.empty.no_categories_message'),
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php else: ?>
    <div class="grid grid-4 shop-categories-grid">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a class="shop-category-card" href="<?php echo e(route('shop.category', $category->slug)); ?>">
                <div class="shop-category-media">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($category->image_path): ?>
                        <?php
                            $categoryImage = \Illuminate\Support\Str::startsWith($category->image_path, ['http://', 'https://', '/'])
                                ? $category->image_path
                                : \Illuminate\Support\Facades\Storage::url($category->image_path);
                        ?>
                        <img src="<?php echo e($categoryImage); ?>" alt="<?php echo e($category->display_name); ?>" loading="lazy">
                    <?php else: ?>
                        <div class="shop-category-placeholder"><?php echo e(\Illuminate\Support\Str::of($category->display_name)->substr(0, 2)->upper()); ?></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="stack" style="gap:0.35rem;">
                    <strong><?php echo e($category->display_name); ?></strong>
                    <p class="small"><?php echo e(__('shop.sections.category_products_count', ['count' => $category->products_count])); ?></p>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php /**PATH C:\xampp\htdocs\makasouk\resources\views/shop/partials/category-blocks.blade.php ENDPATH**/ ?>