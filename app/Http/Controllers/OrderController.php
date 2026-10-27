<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    /**
     * Store a newly created order from customer self-ordering or cashier POS.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:30',
            'customer_email' => 'nullable|email|max:100',
            'table_number' => 'nullable|integer|min:1',
            'order_type' => 'required|in:dine-in,take-away',
            'payment_method' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'service_fee' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'voucher_code' => 'nullable|string|max:50',
            'total_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer',
            'items.*.product_name' => 'required|string|max:150',
            'items.*.category' => 'nullable|string|max:50',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // Generate readable unique order code, e.g. PRJ-20261027-4821 or PRJ-7X8K2
            $randomPart = strtoupper(Str::random(5));
            $orderCode = 'PRJ-' . date('md') . '-' . $randomPart;

            $paymentStatus = in_array(strtolower($validated['payment_method']), ['qris', 'qris instant']) ? 'paid' : 'pending';

            $order = Order::create([
                'order_code' => $orderCode,
                'table_number' => $validated['order_type'] === 'dine-in' ? ($validated['table_number'] ?? null) : null,
                'order_type' => $validated['order_type'],
                'customer_name' => trim($validated['customer_name']),
                'customer_phone' => trim($validated['customer_phone']),
                'customer_email' => !empty($validated['customer_email']) ? trim($validated['customer_email']) : null,
                'subtotal' => $validated['subtotal'],
                'service_fee' => $validated['service_fee'] ?? 0,
                'discount' => $validated['discount'] ?? 0,
                'voucher_code' => $validated['voucher_code'] ?? null,
                'total_price' => $validated['total_price'],
                'payment_method' => $validated['payment_method'],
                'payment_status' => $paymentStatus,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $qty = (int) $item['quantity'];
                $price = (float) $item['price'];
                $sub = $qty * $price;

                $order->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'category' => $item['category'] ?? $this->detectCategoryFromName($item['product_name']),
                    'price' => $price,
                    'quantity' => $qty,
                    'subtotal' => $sub,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            $order->load('items');

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat!',
                'order' => [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'table_number' => $order->table_number,
                    'order_type' => $order->order_type,
                    'customer_name' => $order->customer_name,
                    'customer_phone' => $order->customer_phone,
                    'total_price' => $order->total_price,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'status' => $order->status,
                    'created_at_formatted' => $order->created_at->format('d M Y, H:i'),
                    'items_count' => $order->items->count(),
                ],
            ], 201);
        });
    }

    /**
     * Get live data for cashier dashboard with auto-polling.
     */
    public function liveUpdates(Request $request): JsonResponse
    {
        $today = Carbon::today();
        
        $status = $request->query('status', 'all');
        $search = $request->query('search', '');
        $orderType = $request->query('order_type', 'all');
        $dateFilter = $request->query('date', 'today'); // 'today', 'all', 'yesterday'

        $query = Order::with('items')->latest();

        if ($dateFilter === 'today') {
            $query->whereDate('created_at', $today);
        } elseif ($dateFilter === 'yesterday') {
            $query->whereDate('created_at', Carbon::yesterday());
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($orderType !== 'all') {
            $query->where('order_type', $orderType);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('table_number', 'like', "%{$search}%");
            });
        }

        $orders = $query->limit(60)->get()->map(function ($order) {
            return [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'table_number' => $order->table_number,
                'order_type' => $order->order_type,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_email' => $order->customer_email,
                'subtotal' => (float)$order->subtotal,
                'service_fee' => (float)$order->service_fee,
                'discount' => (float)$order->discount,
                'voucher_code' => $order->voucher_code,
                'total_price' => (float)$order->total_price,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'status' => $order->status,
                'notes' => $order->notes,
                'created_at' => $order->created_at->toIso8601String(),
                'created_at_human' => $order->created_at->diffForHumans(),
                'created_at_time' => $order->created_at->format('H:i'),
                'created_at_full' => $order->created_at->format('d M Y, H:i'),
                'items' => $order->items->map(fn($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'category' => $item->category,
                    'price' => (float)$item->price,
                    'quantity' => (int)$item->quantity,
                    'subtotal' => (float)$item->subtotal,
                    'notes' => $item->notes,
                ]),
            ];
        });

        // Global status counts for today
        $todayOrders = Order::whereDate('created_at', $today);
        $counts = [
            'all' => (clone $todayOrders)->count(),
            'pending' => (clone $todayOrders)->where('status', 'pending')->count(),
            'processing' => (clone $todayOrders)->where('status', 'processing')->count(),
            'completed' => (clone $todayOrders)->where('status', 'completed')->count(),
            'cancelled' => (clone $todayOrders)->where('status', 'cancelled')->count(),
        ];

        $todayRevenue = (clone $todayOrders)
            ->where('status', '!=', 'cancelled')
            ->sum('total_price');

        $latestId = Order::max('id') ?? 0;

        return response()->json([
            'success' => true,
            'orders' => $orders,
            'counts' => $counts,
            'today_revenue' => (float)$todayRevenue,
            'latest_id' => $latestId,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Update order status (pending -> processing -> completed / cancelled).
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        $order->status = $validated['status'];
        
        // If marked completed and payment was pending, optionally auto-mark paid or let cashier do it
        if ($validated['status'] === 'completed' && $order->payment_status === 'pending') {
            $order->payment_status = 'paid';
        }

        $order->save();

        return response()->json([
            'success' => true,
            'message' => "Status pesanan {$order->order_code} berhasil diperbarui menjadi {$order->status}!",
            'order' => $order,
        ]);
    }

    /**
     * Update order payment status (pending -> paid).
     */
    public function updatePayment(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:pending,paid',
        ]);

        $order->payment_status = $validated['payment_status'];
        $order->save();

        return response()->json([
            'success' => true,
            'message' => "Status pembayaran pesanan {$order->order_code} diperbarui menjadi " . ($order->payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS') . "!",
            'order' => $order,
        ]);
    }

    /**
     * Get single order details.
     */
    public function show(Order $order): JsonResponse
    {
        $order->load('items');
        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }

    /**
     * Comprehensive Analytics API data.
     */
    public function analytics(Request $request): JsonResponse
    {
        $period = $request->query('period', 'today'); // 'today', '7days', 'month', 'all'
        
        $query = Order::query()->where('status', '!=', 'cancelled');
        $allOrdersQuery = Order::query();

        $now = Carbon::now();

        if ($period === 'today') {
            $query->whereDate('created_at', Carbon::today());
            $allOrdersQuery->whereDate('created_at', Carbon::today());
            $periodLabel = 'Hari Ini (' . $now->translatedFormat('d M Y') . ')';
        } elseif ($period === '7days') {
            $query->where('created_at', '>=', Carbon::now()->subDays(7)->startOfDay());
            $allOrdersQuery->where('created_at', '>=', Carbon::now()->subDays(7)->startOfDay());
            $periodLabel = '7 Hari Terakhir';
        } elseif ($period === 'month') {
            $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
            $allOrdersQuery->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
            $periodLabel = 'Bulan Ini (' . $now->translatedFormat('F Y') . ')';
        } else {
            $periodLabel = 'Semua Waktu';
        }

        $totalRevenue = (float) (clone $query)->sum('total_price');
        $totalOrders = (clone $allOrdersQuery)->count();
        $completedOrders = (clone $allOrdersQuery)->where('status', 'completed')->count();
        $processingOrders = (clone $allOrdersQuery)->where('status', 'processing')->count();
        $pendingOrders = (clone $allOrdersQuery)->where('status', 'pending')->count();
        $cancelledOrders = (clone $allOrdersQuery)->where('status', 'cancelled')->count();

        $aov = $totalOrders > 0 ? round($totalRevenue / max(1, ($totalOrders - $cancelledOrders))) : 0;
        $successRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 100;

        // Top 10 selling products in selected period
        $orderIds = (clone $query)->pluck('id');
        $topProducts = OrderItem::whereIn('order_id', $orderIds)
            ->select('product_name', 'category', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
            ->groupBy('product_name', 'category')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // Category breakdown
        $categoryBreakdown = OrderItem::whereIn('order_id', $orderIds)
            ->select('category', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
            ->groupBy('category')
            ->orderByDesc('total_sales')
            ->get();

        // Order Type distribution (Dine-in vs Take-away)
        $orderTypes = (clone $allOrdersQuery)
            ->select('order_type', DB::raw('count(*) as count'), DB::raw('SUM(total_price) as revenue'))
            ->groupBy('order_type')
            ->get();

        // Payment Method distribution
        $paymentMethods = (clone $allOrdersQuery)
            ->select('payment_method', DB::raw('count(*) as count'), DB::raw('SUM(total_price) as revenue'))
            ->groupBy('payment_method')
            ->get();

        // Sales trend for charts
        $salesTrend = [];
        if ($period === 'today') {
            // Group by 2-hour slots for today
            for ($h = 8; $h <= 24; $h += 2) {
                $startHour = sprintf('%02d:00', $h - 2);
                $endHour = sprintf('%02d:00', $h);
                $slotLabel = "{$startHour}-{$endHour}";
                
                $slotOrders = (clone $query)
                    ->whereTime('created_at', '>=', $startHour . ':00')
                    ->whereTime('created_at', '<', ($h === 24 ? '23:59:59' : $endHour . ':00'));

                $salesTrend[] = [
                    'label' => $slotLabel,
                    'orders' => (clone $slotOrders)->count(),
                    'revenue' => (float)(clone $slotOrders)->sum('total_price'),
                ];
            }
        } else {
            // Daily trend for 7 days or month
            $days = $period === '7days' ? 7 : ($period === 'month' ? 30 : 14);
            for ($d = $days - 1; $d >= 0; $d--) {
                $targetDate = Carbon::today()->subDays($d);
                $dateLabel = $targetDate->format('d M');
                
                $dayOrders = Order::whereDate('created_at', $targetDate)
                    ->where('status', '!=', 'cancelled');

                $salesTrend[] = [
                    'label' => $dateLabel,
                    'orders' => (clone $dayOrders)->count(),
                    'revenue' => (float)(clone $dayOrders)->sum('total_price'),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'period' => $period,
            'period_label' => $periodLabel,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_orders' => $totalOrders,
                'completed_orders' => $completedOrders,
                'processing_orders' => $processingOrders,
                'pending_orders' => $pendingOrders,
                'cancelled_orders' => $cancelledOrders,
                'average_order_value' => $aov,
                'success_rate' => $successRate,
            ],
            'top_products' => $topProducts,
            'category_breakdown' => $categoryBreakdown,
            'order_types' => $orderTypes,
            'payment_methods' => $paymentMethods,
            'sales_trend' => $salesTrend,
        ]);
    }

    /**
     * Export orders to CSV for Excel / reporting.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $fileName = 'Laporan-Penjualan-PARJO-' . date('Y-m-d_His') . '.csv';

        $orders = Order::with('items')->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Header
            fputcsv($handle, [
                'No',
                'Kode Pesanan',
                'Waktu Pesanan',
                'Tipe Pesanan',
                'No Meja',
                'Nama Pelanggan',
                'WhatsApp',
                'Jumlah Item',
                'Rincian Menu',
                'Subtotal (Rp)',
                'Biaya Layanan (Rp)',
                'Diskon (Rp)',
                'Total Bayar (Rp)',
                'Metode Pembayaran',
                'Status Pembayaran',
                'Status Pesanan',
                'Catatan',
            ]);

            $no = 1;
            foreach ($orders as $order) {
                $itemList = $order->items->map(function ($i) {
                    $note = $i->notes ? " ({$i->notes})" : '';
                    return "{$i->quantity}x {$i->product_name}{$note}";
                })->implode('; ');

                fputcsv($handle, [
                    $no++,
                    $order->order_code,
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->order_type === 'dine-in' ? 'Makan di Tempat' : 'Bawa Pulang',
                    $order->table_number ?? '-',
                    $order->customer_name,
                    $order->customer_phone,
                    $order->items->sum('quantity'),
                    $itemList,
                    $order->subtotal,
                    $order->service_fee,
                    $order->discount,
                    $order->total_price,
                    $order->payment_method,
                    $order->payment_status === 'paid' ? 'LUNAS' : 'MENUNGGU',
                    strtoupper($order->status),
                    $order->notes ?? '-',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper to detect category from product name if category not explicitly passed.
     */
    private function detectCategoryFromName(string $name): string
    {
        $lower = strtolower($name);
        if (str_contains($lower, 'pancong')) return 'Pancong';
        if (str_contains($lower, 'kopi') || str_contains($lower, 'coffee') || str_contains($lower, 'beer')) return 'Coffee';
        if (str_contains($lower, 'teh') || str_contains($lower, 'tea')) return 'Tea';
        if (str_contains($lower, 'indomie') || str_contains($lower, 'mie')) return 'Indomie';
        if (str_contains($lower, 'ayam')) return 'Ayam';
        if (str_contains($lower, 'kulit')) return 'Kulit';
        if (str_contains($lower, 'telor') || str_contains($lower, 'telur')) return 'Telor';
        if (str_contains($lower, 'burjo') || str_contains($lower, 'nasi')) return 'Burjo & Nasi';
        if (str_contains($lower, 'snack') || str_contains($lower, 'kentang') || str_contains($lower, 'cireng') || str_contains($lower, 'pisang')) return 'Snack';
        if (str_contains($lower, 'milo') || str_contains($lower, 'nutrisari') || str_contains($lower, 'sachet') || str_contains($lower, 'chocolatos')) return 'Sachet';
        if (str_contains($lower, 'topping')) return 'Topping';
        return 'Signature';
    }
}
