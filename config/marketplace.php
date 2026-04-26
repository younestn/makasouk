<?php

return [
    'commission_rate' => (float) env('MARKETPLACE_COMMISSION_RATE', 15),
    'default_shipping_amount' => (float) env('MARKETPLACE_DEFAULT_SHIPPING_AMOUNT', 0),
];
