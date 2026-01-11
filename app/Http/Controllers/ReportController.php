<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display reports index page.
     */
    public function index(Request $request)
    {
        // Set default period to current month
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        // Get paid transactions within the period
        $transactions = Transaction::with(['customer', 'service'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->orderBy('transaction_date', 'desc')
            ->get();

        // Calculate metrics
        $totalRevenue = $transactions->sum('total_amount');
        $totalTransactions = $transactions->count();
        $operationalCosts = $totalRevenue * 0.6; // 60% operational costs assumption
        $netProfit = $totalRevenue - $operationalCosts;

        // Calculate service revenue breakdown
        $servicesRevenue = [];
        foreach ($transactions->groupBy('service_id') as $serviceTransactions) {
            $service = $serviceTransactions->first()->service;
            $servicesRevenue[$service->name ?? 'Unknown'] = $serviceTransactions->sum('total_amount');
        }
        arsort($servicesRevenue);

        return view('reports.index', compact(
            'transactions',
            'totalRevenue',
            'totalTransactions',
            'operationalCosts',
            'netProfit',
            'servicesRevenue',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display profit loss report.
     */
    public function profitLoss(Request $request)
    {
        // Gunakan PHP native date functions
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        $transactions = Transaction::with(['customer', 'service'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->orderBy('transaction_date', 'desc')
            ->get();

        $totalRevenue = $transactions->sum('total_amount');
        $totalTransactions = $transactions->count();

        // Asumsi: 60% dari revenue adalah biaya operasional
        $operationalCosts = $totalRevenue * 0.6;
        $netProfit = $totalRevenue - $operationalCosts;

        return view('reports.profit-loss', compact(
            'transactions',
            'totalRevenue',
            'totalTransactions',
            'operationalCosts',
            'netProfit',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export reports.
     */
    public function export(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        // Get data for export
        $transactions = Transaction::with(['customer', 'service'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->orderBy('transaction_date', 'desc')
            ->get();

        $totalRevenue = $transactions->sum('total_amount');
        $totalTransactions = $transactions->count();
        $operationalCosts = $totalRevenue * 0.6;
        $netProfit = $totalRevenue - $operationalCosts;
        $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        // Format transactions for export (convert to array with calculated avg_transaction)
        $formattedTransactions = $transactions->map(function ($transaction) {
            return [
                'invoice_number' => $transaction->invoice_number,
                'customer_name' => $transaction->customer->name ?? 'N/A',
                'customer_phone' => $transaction->customer->phone ?? 'N/A',
                'service_name' => $transaction->service->name ?? 'N/A',
                'quantity' => $transaction->quantity,
                'price' => $transaction->price,
                'total_amount' => $transaction->total_amount,
                'transaction_date' => $transaction->transaction_date,
                'payment_method' => $transaction->payment_method,
                'payment_status' => $transaction->payment_status
            ];
        })->toArray();

        // Return data for API (JavaScript will handle the export)
        return response()->json([
            'success' => true,
            'data' => [
                'transactions' => $formattedTransactions,
                'summary' => [
                    'total_revenue' => (float) $totalRevenue,
                    'total_transactions' => (int) $totalTransactions,
                    'avg_transaction' => $totalTransactions > 0 ? round($totalRevenue / $totalTransactions) : 0,
                    'operational_costs' => (float) $operationalCosts,
                    'net_profit' => (float) $netProfit,
                    'profit_margin' => (float) $profitMargin,
                    'period' => [
                        'start' => $startDate,
                        'end' => $endDate
                    ]
                ]
            ]
        ]);
    }

    /**
     * Get report data for API (for charts)
     */
    public function getReportData(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        // Get transactions data
        $transactions = Transaction::with(['customer', 'service'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->orderBy('transaction_date', 'asc')
            ->get();

        // Calculate daily revenue for chart
        $dailyData = [];
        $currentDate = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($currentDate->lte($end)) {
            $dateStr = $currentDate->format('Y-m-d');
            $dailyTotal = $transactions
                ->where('transaction_date', $dateStr)
                ->sum('total_amount');

            $dailyData[] = [
                'date' => $currentDate->format('d M'),
                'revenue' => $dailyTotal,
                'transactions' => $transactions->where('transaction_date', $dateStr)->count()
            ];

            $currentDate->addDay();
        }

        // Calculate service distribution
        $serviceData = [];
        $services = $transactions->groupBy('service_id');

        foreach ($services as $serviceTransactions) {
            $service = $serviceTransactions->first()->service;
            $serviceData[] = [
                'name' => $service->name,
                'revenue' => $serviceTransactions->sum('total_amount'),
                'transactions' => $serviceTransactions->count(),
                'percentage' => $transactions->sum('total_amount') > 0
                    ? round(($serviceTransactions->sum('total_amount') / $transactions->sum('total_amount')) * 100, 1)
                    : 0
            ];
        }

        // Payment method distribution
        $paymentData = [
            'cash' => $transactions->where('payment_method', 'cash')->count(),
            'midtrans' => $transactions->where('payment_method', 'midtrans')->count(),
            'transfer' => $transactions->where('payment_method', 'transfer')->count()
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'daily' => $dailyData,
                'services' => $serviceData,
                'payments' => $paymentData,
                'summary' => [
                    'total_revenue' => $transactions->sum('total_amount'),
                    'total_transactions' => $transactions->count(),
                    'avg_transaction' => $transactions->count() > 0
                        ? round($transactions->sum('total_amount') / $transactions->count())
                        : 0,
                    'operational_costs' => $transactions->sum('total_amount') * 0.6,
                    'net_profit' => $transactions->sum('total_amount') * 0.4
                ]
            ]
        ]);
    }
}
