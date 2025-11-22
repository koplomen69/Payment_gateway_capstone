<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display dashboard.
     */
    public function index()
    {
        try {
            $totalRevenue = Transaction::where('payment_status', 'paid')->sum('total_amount');
            $totalTransactions = Transaction::count();
            $totalCustomers = Customer::count();
            $totalServices = Service::where('is_active', true)->count();

            $recentTransactions = Transaction::with(['customer', 'service'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return view('dashboard', compact(
                'totalRevenue',
                'totalTransactions',
                'totalCustomers',
                'totalServices',
                'recentTransactions'
            ));
        } catch (\Exception $e) {
            // Fallback jika ada error
            return view('dashboard', [
                'totalRevenue' => 0,
                'totalTransactions' => 0,
                'totalCustomers' => 0,
                'totalServices' => 0,
                'recentTransactions' => collect()
            ]);
        }
    }
}
