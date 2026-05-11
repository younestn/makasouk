<?php
    $isRtl = $locale === 'ar';
?>

<!doctype html>
<html lang="<?php echo e($locale); ?>" dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>">
<body style="font-family: <?php echo e($isRtl ? 'Cairo, ' : ''); ?>Arial, sans-serif; color: #1f2937; background: #f8f5ef; padding: 24px;">
    <div style="max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 18px; padding: 28px; border: 1px solid #eadfca;">
        <p style="margin: 0 0 8px; color: #9a6a20; font-weight: 700;">
            <?php echo e(__('messages.mail.incoming_tailor_order_badge', [], $locale)); ?>

        </p>

        <h1 style="margin: 0 0 16px; font-size: 24px;">
            <?php echo e(__('messages.mail.incoming_tailor_order_title', ['order' => $order->id], $locale)); ?>

        </h1>

        <p style="line-height: 1.7;">
            <?php echo e(__('messages.mail.incoming_tailor_order_intro', ['name' => $tailor->name], $locale)); ?>

        </p>

        <div style="background: #fbf7ee; border-radius: 14px; padding: 18px; margin: 18px 0;">
            <p><strong><?php echo e(__('messages.mail.product', [], $locale)); ?>:</strong> <?php echo e($order->product?->name ?? '-'); ?></p>
            <p><strong><?php echo e(__('messages.mail.category', [], $locale)); ?>:</strong> <?php echo e($order->product?->category?->name ?? '-'); ?></p>
            <p><strong><?php echo e(__('messages.mail.delivery_wilaya', [], $locale)); ?>:</strong> <?php echo e($order->delivery_work_wilaya ?? '-'); ?></p>
            <p><strong><?php echo e(__('messages.mail.net_earnings', [], $locale)); ?>:</strong> <?php echo e(number_format((float) $financials['tailor_net_amount'], 2)); ?> MAD</p>
        </div>

        <a href="<?php echo e($dashboardUrl); ?>" style="display: inline-block; background: #b8872d; color: #ffffff; text-decoration: none; border-radius: 999px; padding: 12px 20px; font-weight: 700;">
            <?php echo e(__('messages.mail.open_dashboard', [], $locale)); ?>

        </a>

        <p style="margin-top: 20px; color: #6b7280; font-size: 13px;">
            <?php echo e(__('messages.mail.shipping_privacy_notice', [], $locale)); ?>

        </p>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\makasouk\resources\views\emails\tailor\incoming-order.blade.php ENDPATH**/ ?>