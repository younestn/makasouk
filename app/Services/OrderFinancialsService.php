<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;

class OrderFinancialsService
{
    /**
     * @return array{subtotal_amount: float, shipping_amount: float, platform_commission_amount: float, tailor_net_amount: float, commission_rate: float}
     */
    public function snapshotForProduct(Product $product, ?float $shippingAmount = null): array
    {
        $subtotal = (float) ($product->sale_price ?? $product->price ?? 0);
        $shipping = $shippingAmount ?? (float) config('marketplace.default_shipping_amount', 0);
        $commissionRate = (float) config('marketplace.commission_rate', 15);
        $commission = round($subtotal * ($commissionRate / 100), 2);

        return [
            'subtotal_amount' => round($subtotal, 2),
            'shipping_amount' => round($shipping, 2),
            'platform_commission_amount' => $commission,
            'tailor_net_amount' => max(0, round($subtotal - $commission, 2)),
            'commission_rate' => $commissionRate,
        ];
    }

    /**
     * @return array{subtotal_amount: float, shipping_amount: float, platform_commission_amount: float, tailor_net_amount: float, total_amount: float}
     */
    public function payload(Order $order): array
    {
        $subtotal = (float) ($order->subtotal_amount ?? $order->product?->sale_price ?? $order->product?->price ?? 0);
        $shipping = (float) ($order->shipping_amount ?? 0);
        $commission = (float) ($order->platform_commission_amount ?? round($subtotal * ((float) config('marketplace.commission_rate', 15) / 100), 2));
        $net = (float) ($order->tailor_net_amount ?? max(0, $subtotal - $commission));

        return [
            'subtotal_amount' => round($subtotal, 2),
            'shipping_amount' => round($shipping, 2),
            'platform_commission_amount' => round($commission, 2),
            'tailor_net_amount' => round($net, 2),
            'total_amount' => round($subtotal + $shipping, 2),
        ];
    }
}
