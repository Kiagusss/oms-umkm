<?php

namespace App\Ai\Tools;

use App\Models\Order;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Carbon;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * GetOrderSummaryTool — ringkasan pesanan (read-only, aman dipanggil admin).
 *
 * Mengembalikan agregat pesanan (total, jumlah order, rata-rata nilai
 * order) untuk periode waktu tertentu. Tidak menampilkan data pribadi
 * pelanggan.
 */
class GetOrderSummaryTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Mengambil ringkasan pesanan: total nilai pesanan, jumlah order, dan rata-rata nilai order untuk periode waktu tertentu. Default 7 hari terakhir.';
    }

    public function handle(Request $request): Stringable|string
    {
        $days = (int) ($request['days'] ?? 7);
        $days = max(1, min($days, 365));

        $since = Carbon::now()->subDays($days);

        $orders = Order::query()
            ->where('created_at', '>=', $since)
            ->get();

        $total = (int) $orders->sum('total');
        $count = $orders->count();
        $average = $count > 0 ? (int) round($total / $count) : 0;

        // Distribusi status
        $byStatus = $orders->groupBy('status')
            ->map(fn ($group) => $group->count())
            ->all();

        return (string) json_encode([
            'period_days' => $days,
            'since' => $since->toDateString(),
            'total_orders' => $count,
            'total_revenue' => $total,
            'average_order_value' => $average,
            'by_status' => $byStatus,
        ], JSON_UNESCAPED_UNICODE);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'days' => $schema->integer()
                ->min(1)->max(365)
                ->description('Periode waktu dalam hari ke belakang. Default 7, maksimum 365.'),
        ];
    }
}
