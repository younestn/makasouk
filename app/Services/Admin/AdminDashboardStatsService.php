<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminDashboardStatsService
{
    /**
     * @return array{
     *     total_users: int,
     *     total_vendors: int,
     *     total_products: int,
     *     total_categories: int,
     *     total_orders: int,
     *     pending_orders: int,
     *     completed_orders: int,
     *     total_revenue: float|null,
     *     low_stock_products: int|null,
     *     recent_registrations: int
     * }
     */
    public function getKpis(): array
    {
        $statusCounts = Order::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'total_users' => User::query()->count(),
            'total_vendors' => User::query()->where('role', User::ROLE_TAILOR)->count(),
            'total_products' => Product::query()->count(),
            'total_categories' => DB::table('categories')->count(),
            'total_orders' => (int) $statusCounts->sum(),
            'pending_orders' => (int) $statusCounts->only($this->pendingOrderStatuses())->sum(),
            'completed_orders' => (int) ($statusCounts[Order::STATUS_COMPLETED] ?? 0),
            'total_revenue' => $this->getTotalRevenue(),
            'low_stock_products' => $this->getLowStockProductsCount(),
            'recent_registrations' => User::query()
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
        ];
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    public function getOrdersTrend(int $days = 14): array
    {
        return $this->buildDailySeries(
            Order::query()
                ->selectRaw('DATE(created_at) as day, COUNT(*) as aggregate')
                ->whereDate('created_at', '>=', now()->subDays($days - 1))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->pluck('aggregate', 'day')
                ->all(),
            $days,
        );
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    public function getUserRegistrationsTrend(int $days = 14): array
    {
        return $this->buildDailySeries(
            User::query()
                ->selectRaw('DATE(created_at) as day, COUNT(*) as aggregate')
                ->whereDate('created_at', '>=', now()->subDays($days - 1))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->pluck('aggregate', 'day')
                ->all(),
            $days,
        );
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, float>}
     */
    public function getRevenueTrend(int $days = 14): array
    {
        if (! $this->supportsRevenueMetrics()) {
            $series = $this->buildDailyFloatSeries([], $days);

            return [
                'labels' => $series['labels'],
                'values' => $series['values'],
            ];
        }

        $valuesByDay = Order::query()
            ->join('products', 'products.id', '=', 'orders.product_id')
            ->selectRaw('DATE(orders.created_at) as day, COALESCE(SUM(products.price), 0) as aggregate')
            ->where('orders.status', Order::STATUS_COMPLETED)
            ->whereDate('orders.created_at', '>=', now()->subDays($days - 1))
            ->groupBy(DB::raw('DATE(orders.created_at)'))
            ->pluck('aggregate', 'day')
            ->all();

        return $this->buildDailyFloatSeries($valuesByDay, $days);
    }

    public function supportsRevenueMetrics(): bool
    {
        return Schema::hasTable('products')
            && Schema::hasColumn('products', 'price')
            && Schema::hasTable('orders')
            && Schema::hasColumn('orders', 'status');
    }

    /**
     * @return array<int, string>
     */
    protected function pendingOrderStatuses(): array
    {
        return [
            Order::STATUS_PENDING,
            Order::STATUS_SEARCHING_FOR_TAILOR,
            Order::STATUS_NO_TAILORS_AVAILABLE,
            Order::STATUS_ACCEPTED,
            Order::STATUS_PROCESSING,
            Order::STATUS_READY_FOR_DELIVERY,
        ];
    }

    protected function getTotalRevenue(): ?float
    {
        if (! $this->supportsRevenueMetrics()) {
            return null;
        }

        return (float) Order::query()
            ->join('products', 'products.id', '=', 'orders.product_id')
            ->where('orders.status', Order::STATUS_COMPLETED)
            ->sum('products.price');
    }

    protected function getLowStockProductsCount(): ?int
    {
        if (! Schema::hasTable('products')) {
            return null;
        }

        if (Schema::hasColumn('products', 'stock')) {
            return Product::query()->where('stock', '<=', 5)->count();
        }

        if (Schema::hasColumn('products', 'stock_quantity')) {
            return Product::query()->where('stock_quantity', '<=', 5)->count();
        }

        return null;
    }

    /**
     * @param  array<string, int|float|string>  $valuesByDay
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    protected function buildDailySeries(array $valuesByDay, int $days): array
    {
        $start = CarbonImmutable::today()->subDays(max($days, 1) - 1);

        $labels = [];
        $values = [];

        for ($offset = 0; $offset < $days; $offset++) {
            $date = $start->addDays($offset);
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d M');
            $values[] = (int) ($valuesByDay[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * @param  array<string, int|float|string>  $valuesByDay
     * @return array{labels: array<int, string>, values: array<int, float>}
     */
    protected function buildDailyFloatSeries(array $valuesByDay, int $days): array
    {
        $start = CarbonImmutable::today()->subDays(max($days, 1) - 1);

        $labels = [];
        $values = [];

        for ($offset = 0; $offset < $days; $offset++) {
            $date = $start->addDays($offset);
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d M');
            $values[] = round((float) ($valuesByDay[$key] ?? 0), 2);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }
}
