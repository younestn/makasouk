<?php
    $currentLocale = app()->getLocale();
    $switchTo = $currentLocale === 'ar' ? 'en' : 'ar';
?>

<a
    href="<?php echo e(route('locale.switch', ['locale' => $switchTo])); ?>"
    class="fi-btn fi-btn-size-sm fi-btn-color-gray fi-btn-outlined"
    style="margin-inline-start: .5rem;"
>
    <?php echo e($currentLocale === 'ar' ? __('admin.layout.english') : __('admin.layout.arabic')); ?>

</a>

<?php /**PATH C:\xampp\htdocs\makasouk\resources\views\filament\partials\locale-switcher.blade.php ENDPATH**/ ?>