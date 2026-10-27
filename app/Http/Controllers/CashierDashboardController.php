<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CashierDashboardController extends Controller
{
    /**
     * Display the main cashier / admin order management dashboard.
     */
    public function index(): View
    {
        $today = Carbon::today();
        
        $pendingCount = Order::whereDate('created_at', $today)->where('status', 'pending')->count();
        $processingCount = Order::whereDate('created_at', $today)->where('status', 'processing')->count();
        $completedCount = Order::whereDate('created_at', $today)->where('status', 'completed')->count();
        $todayRevenue = Order::whereDate('created_at', $today)->where('status', '!=', 'cancelled')->sum('total_price');

        return view('cashier.dashboard', compact(
            'pendingCount',
            'processingCount',
            'completedCount',
            'todayRevenue'
        ));
    }
}
