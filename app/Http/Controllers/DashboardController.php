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
        $totalRevenue = Transaction::successful()->sum('total_amount');
        $totalTransactions = Transaction::count();
        $totalCustomers = Customer::count();
        $totalServices = Service::active()->count();

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
    }
}
