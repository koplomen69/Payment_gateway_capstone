<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display reports index page.
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Display profit loss report.
     */
    public function profitLoss(Request $request)
    {
        // Gunakan PHP native date functions
        $startDate = $request->get('start_date', date('Y-m-01')); // First day of current month
        $endDate = $request->get('end_date', date('Y-m-d')); // Today

        $transactions = Transaction::with(['customer', 'service'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('payment_status', 'paid')
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
        $type = $request->get('type', 'profit-loss');
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        // Untuk sementara redirect ke profit-loss
        // Nanti bisa diimplementasi export PDF/Excel
        return redirect()->route('reports.profit-loss', [
            'start_date' => $startDate,
            'end_date' => $endDate
        ])->with('info', 'Fitur export akan segera tersedia.');
    }
}
