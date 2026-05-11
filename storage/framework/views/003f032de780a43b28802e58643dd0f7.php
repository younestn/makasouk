<?php
    $isRtl = $locale === 'ar';
?>
<!doctype html>
<html lang="<?php echo e($locale); ?>" dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(__('messages.mail.test_title', [], $locale)); ?></title>
</head>
<body style="margin:0;background:#f7f3ea;font-family:<?php echo e($isRtl ? 'Cairo, ' : ''); ?>Arial,sans-serif;color:#2b2118;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#fff;border:1px solid #eadfc9;border-radius:18px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 12px;color:#a8792d;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">
                                MAKASOUK
                            </p>
                            <h1 style="margin:0 0 12px;font-size:24px;line-height:1.3;">
                                <?php echo e(__('messages.mail.test_title', [], $locale)); ?>

                            </h1>
                            <p style="margin:0;color:#6f6255;line-height:1.8;">
                                <?php echo e(__('messages.mail.test_body', [], $locale)); ?>

                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\makasouk\resources\views\emails\admin\test-mail-settings.blade.php ENDPATH**/ ?>