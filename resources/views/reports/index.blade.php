@extends('layouts.app')

@section('title', 'Laporan Keuangan - Ananda Laundry')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Laporan Keuangan</h1>
                <p class="text-gray-600 mt-2">Analisis lengkap performa bisnis Ananda Laundry</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button onclick="printReport()"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg flex items-center space-x-2 transition duration-200 shadow-md">
                    <i class="fas fa-print"></i>
                    <span>Cetak Laporan</span>
                </button>
                <button onclick="exportToPDF()"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg flex items-center space-x-2 transition duration-200 shadow-md">
                    <i class="fas fa-file-pdf"></i>
                    <span>Export PDF</span>
                </button>
                <button onclick="exportToExcel()"
                        class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2.5 rounded-lg flex items-center space-x-2 transition duration-200 shadow-md">
                    <i class="fas fa-file-excel"></i>
                    <span>Export Excel</span>
                </button>
                <a href="{{ route('dashboard') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2.5 rounded-lg flex items-center space-x-2 transition duration-200 shadow-md">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Print Header (only visible when printing) -->
    <div class="print-only bg-white p-8 hidden">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold">ANANDA LAUNDRY</h1>
            <p class="text-lg">Laporan Keuangan Lengkap</p>
            <p class="text-gray-600 mt-2">Periode: <span id="print-period">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span></p>
            <p class="text-gray-600">Tanggal Cetak: <span id="print-date">{{ date('d/m/Y H:i') }}</span></p>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8 no-print">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-filter text-blue-500 mr-2"></i>
            Filter Periode Laporan
        </h2>

        <form id="filterForm" action="{{ route('reports.profit-loss') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i> Pilih Periode
                    </label>
                    <select id="periodSelect" name="period" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Hari Ini</option>
                        <option value="week" {{ request('period') == 'week' || !request('period') ? 'selected' : '' }}>Minggu Ini</option>
                        <option value="month" {{ request('period') == 'month' || !request('period') ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="quarter" {{ request('period') == 'quarter' ? 'selected' : '' }}>Triwulan Ini</option>
                        <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>Tahun Ini</option>
                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Tanggal Kustom</option>
                    </select>
                </div>

                <div id="customDateRange" class="{{ request('period') == 'custom' ? 'md:col-span-2' : 'hidden' }}">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-day mr-1"></i> Dari Tanggal
                            </label>
                            <input type="date" id="startDate" name="start_date"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   value="{{ request('start_date', $startDate) }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-day mr-1"></i> Sampai Tanggal
                            </label>
                            <input type="date" id="endDate" name="end_date"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   value="{{ request('end_date', $endDate) }}">
                        </div>
                    </div>
                </div>

                <div class="flex items-end">
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 px-4 rounded-lg flex items-center justify-center space-x-2 transition duration-200 shadow-md">
                        <i class="fas fa-chart-line"></i>
                        <span>Tampilkan Laporan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Report Data Section -->
    <div id="reportContent">
        <!-- Quick Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Revenue Card -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl shadow-lg p-6 transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-90">Total Pendapatan</p>
                        <p class="text-3xl font-bold mt-2">
                            Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                        </p>
                        <div class="flex items-center mt-2">
                            <i class="fas fa-arrow-up text-green-300 mr-1"></i>
                            <span class="text-sm opacity-90">Gross Revenue</span>
                        </div>
                    </div>
                    <div class="bg-white bg-opacity-20 p-3 rounded-full">
                        <i class="fas fa-wallet text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Transactions Card -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl shadow-lg p-6 transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-90">Total Transaksi</p>
                        <p class="text-3xl font-bold mt-2">{{ $totalTransactions }}</p>
                        <div class="flex items-center mt-2">
                            <i class="fas fa-receipt mr-1"></i>
                            <span class="text-sm opacity-90">Transaksi Lunas</span>
                        </div>
                    </div>
                    <div class="bg-white bg-opacity-20 p-3 rounded-full">
                        <i class="fas fa-shopping-cart text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Average Transaction Card -->
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl shadow-lg p-6 transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-90">Rata-rata Transaksi</p>
                        <p class="text-3xl font-bold mt-2">
                            Rp {{ number_format($totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0, 0, ',', '.') }}
                        </p>
                        <div class="flex items-center mt-2">
                            <i class="fas fa-chart-bar mr-1"></i>
                            <span class="text-sm opacity-90">Per Transaksi</span>
                        </div>
                    </div>
                    <div class="bg-white bg-opacity-20 p-3 rounded-full">
                        <i class="fas fa-chart-line text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Net Profit Card -->
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl shadow-lg p-6 transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-90">Profit Bersih</p>
                        <p class="text-3xl font-bold mt-2">
                            Rp {{ number_format($netProfit, 0, ',', '.') }}
                        </p>
                        <div class="flex items-center mt-2">
                            <i class="fas fa-hand-holding-usd mr-1"></i>
                            <span class="text-sm opacity-90">Net Income</span>
                        </div>
                    </div>
                    <div class="bg-white bg-opacity-20 p-3 rounded-full">
                        <i class="fas fa-money-bill-wave text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profit Loss Analysis -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-chart-pie text-blue-500 mr-2"></i>
                    Analisis Profit & Loss
                </h2>
                <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                    Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Profit Breakdown -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Breakdown Pendapatan</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-4 bg-blue-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                                <span class="font-medium">Pendapatan Kotor</span>
                            </div>
                            <span class="font-bold text-blue-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between items-center p-4 bg-yellow-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                                <span class="font-medium">Biaya Operasional</span>
                            </div>
                            <span class="font-bold text-yellow-600">Rp {{ number_format($operationalCosts, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between items-center p-4 bg-green-50 rounded-lg border-2 border-green-200">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                                <span class="font-medium">Profit Bersih</span>
                            </div>
                            <span class="font-bold text-green-600 text-xl">Rp {{ number_format($netProfit, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Profit Margin -->
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-medium text-gray-700">Profit Margin</span>
                            <span class="font-bold text-green-600">
                                {{ $totalRevenue > 0 ? number_format(($netProfit / $totalRevenue) * 100, 1) : 0 }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full"
                                 style="width: {{ $totalRevenue > 0 ? min(($netProfit / $totalRevenue) * 100, 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Operational Efficiency -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Efisiensi Operasional</h3>
                    <div class="space-y-6">
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">Biaya Operasional vs Revenue</span>
                                <span class="text-sm font-bold text-gray-700">
                                    {{ $totalRevenue > 0 ? number_format(($operationalCosts / $totalRevenue) * 100, 1) : 0 }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-yellow-500 h-3 rounded-full"
                                     style="width: {{ $totalRevenue > 0 ? min(($operationalCosts / $totalRevenue) * 100, 100) : 0 }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Target optimal: ≤ 60%</p>
                        </div>

                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">Revenue per Transaksi</span>
                                <span class="text-sm font-bold text-gray-700">
                                    Rp {{ number_format($totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-blue-500 h-3 rounded-full"
                                     style="width: {{ min(($totalTransactions > 0 ? ($totalRevenue / $totalTransactions) / 100000 * 100 : 0), 100) }}%"></div>
                            </div>
                        </div>

                        <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                            <h4 class="font-semibold text-indigo-800 mb-2 flex items-center">
                                <i class="fas fa-lightbulb mr-2"></i>
                                Insight Bisnis
                            </h4>
                            <p class="text-sm text-indigo-700">
                                @if($netProfit > 0 && $totalRevenue > 0)
                                    @php
                                        $profitMargin = ($netProfit / $totalRevenue) * 100;
                                        $operationalRatio = ($operationalCosts / $totalRevenue) * 100;
                                    @endphp
                                    @if($profitMargin >= 30)
                                        <span class="font-semibold">✅ Excellent!</span> Profit margin sebesar {{ number_format($profitMargin, 1) }}% menunjukkan performa bisnis yang sangat sehat.
                                    @elseif($profitMargin >= 20)
                                        <span class="font-semibold">✓ Baik!</span> Profit margin sebesar {{ number_format($profitMargin, 1) }}% menunjukkan bisnis berjalan dengan baik.
                                    @else
                                        <span class="font-semibold">⚠️ Perlu Perhatian!</span> Profit margin sebesar {{ number_format($profitMargin, 1) }}% masih di bawah standar optimal.
                                    @endif

                                    @if($operationalRatio > 60)
                                        <br><span class="font-semibold">⚠️ Perhatian!</span> Biaya operasional sudah mencapai {{ number_format($operationalRatio, 1) }}%, pertimbangkan efisiensi.
                                    @endif
                                @else
                                    Mulai dengan meningkatkan jumlah transaksi untuk melihat analisis yang lebih akurat.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Recent Transactions -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-history text-blue-500 mr-2"></i>
                        Transaksi Terbaru
                    </h3>
                    <a href="{{ route('transactions.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Layanan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($transactions->take(10) as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $transaction->invoice_number }}</div>
                                    <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $transaction->customer->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $transaction->customer->phone }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $transaction->service->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $transaction->quantity }} kg</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-bold text-green-600">
                                        Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($transaction->payment_status == 'paid')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> LUNAS
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-clock mr-1"></i> BELUM
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    <i class="fas fa-receipt text-3xl mb-3 block text-gray-300"></i>
                                    Belum ada transaksi pada periode ini
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Service Performance -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-star text-yellow-500 mr-2"></i>
                    Top Layanan
                </h3>

                <div class="space-y-4">
                    @php
                        $servicesRevenue = [];
                        $servicesCount = [];
                        foreach($transactions as $transaction) {
                            $serviceName = $transaction->service->name;
                            if (!isset($servicesRevenue[$serviceName])) {
                                $servicesRevenue[$serviceName] = 0;
                                $servicesCount[$serviceName] = 0;
                            }
                            $servicesRevenue[$serviceName] += $transaction->total_amount;
                            $servicesCount[$serviceName] += 1;
                        }
                        arsort($servicesRevenue);
                        $topServices = array_slice($servicesRevenue, 0, 5, true);
                    @endphp

                    @forelse($topServices as $serviceName => $revenue)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-concierge-bell text-blue-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ $serviceName }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $servicesCount[$serviceName] ?? 0 }} transaksi
                                    @if($totalRevenue > 0)
                                    • {{ number_format($revenue / $totalRevenue * 100, 1) }}% dari total
                                    @endif
                                </p>
                            </div>
                        </div>
                        <span class="font-bold text-green-600">Rp {{ number_format($revenue, 0, ',', '.') }}</span>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-chart-pie text-3xl mb-3 block text-gray-300"></i>
                        Tidak ada data layanan
                    </div>
                    @endforelse
                </div>

                <!-- Summary Box -->
                <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-lg">
                    <h4 class="font-semibold text-blue-800 mb-2 flex items-center">
                        <i class="fas fa-chart-line mr-2"></i>
                        Statistik Ringkas
                    </h4>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-gray-600">Total Transaksi</p>
                            <p class="font-bold text-gray-800">{{ $totalTransactions }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Jenis Layanan</p>
                            <p class="font-bold text-gray-800">{{ count($servicesRevenue ?? []) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Report for Print -->
    <div class="print-only bg-white p-6 border rounded-lg hidden">
        <h3 class="text-xl font-bold mb-4 border-b pb-2">Ringkasan Laporan Keuangan</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p><strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
                <p><strong>Total Pendapatan:</strong> Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                <p><strong>Total Transaksi:</strong> {{ $totalTransactions }}</p>
                <p><strong>Rata-rata Transaksi:</strong> Rp {{ number_format($totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0, 0, ',', '.') }}</p>
            </div>
            <div>
                <p><strong>Biaya Operasional:</strong> Rp {{ number_format($operationalCosts, 0, ',', '.') }}</p>
                <p><strong>Profit Bersih:</strong> Rp {{ number_format($netProfit, 0, ',', '.') }}</p>
                <p><strong>Profit Margin:</strong> {{ $totalRevenue > 0 ? number_format(($netProfit / $totalRevenue) * 100, 1) : 0 }}%</p>
                <p><strong>Biaya Operasional/Revenue:</strong> {{ $totalRevenue > 0 ? number_format(($operationalCosts / $totalRevenue) * 100, 1) : 0 }}%</p>
            </div>
        </div>

        <!-- Detail Transaksi untuk Print -->
        @if($transactions->count() > 0)
        <div class="mt-6">
            <h4 class="font-bold mb-2">Detail Transaksi ({{ $transactions->count() }} transaksi)</h4>
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-3 py-2 text-left">No</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Invoice</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Pelanggan</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Layanan</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Total</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Tanggal</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions->take(20) as $index => $transaction)
                    <tr>
                        <td class="border border-gray-300 px-3 py-2">{{ $index + 1 }}</td>
                        <td class="border border-gray-300 px-3 py-2">{{ $transaction->invoice_number }}</td>
                        <td class="border border-gray-300 px-3 py-2">{{ $transaction->customer->name }}</td>
                        <td class="border border-gray-300 px-3 py-2">{{ $transaction->service->name }}</td>
                        <td class="border border-gray-300 px-3 py-2">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                        <td class="border border-gray-300 px-3 py-2">{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }}</td>
                        <td class="border border-gray-300 px-3 py-2">{{ $transaction->payment_status == 'paid' ? 'LUNAS' : 'BELUM' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50">
                        <td colspan="4" class="border border-gray-300 px-3 py-2 font-bold text-right">TOTAL:</td>
                        <td class="border border-gray-300 px-3 py-2 font-bold">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                        <td colspan="2" class="border border-gray-300 px-3 py-2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .print-only {
        display: none;
    }
    .no-print {
        display: block;
    }

    @media print {
        .no-print {
            display: none !important;
        }
        .print-only {
            display: block !important;
        }
        body {
            background: white !important;
            font-size: 11pt;
            font-family: 'Arial', sans-serif;
        }
        .container {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .shadow-lg, .shadow-md, .shadow {
            box-shadow: none !important;
        }
        .bg-gradient-to-r {
            background: #f8fafc !important;
            color: black !important;
            border: 1px solid #e2e8f0 !important;
        }
        .rounded-xl, .rounded-lg, .rounded {
            border-radius: 0 !important;
        }
        .border {
            border: 1px solid #e2e8f0 !important;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .transform {
            transform: none !important;
        }
        .transition-transform {
            transition: none !important;
        }
        .hover\:scale-105:hover {
            transform: none !important;
        }
        .hover\:bg-gray-100:hover {
            background: white !important;
        }
    }
</style>
@endpush

@push('scripts')
<!-- Library untuk export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.5.28/dist/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const periodSelect = document.getElementById('periodSelect');
        const customDateRange = document.getElementById('customDateRange');
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');

        // Handle period selection
        periodSelect.addEventListener('change', function() {
            const today = new Date();

            if (this.value === 'custom') {
                customDateRange.classList.remove('hidden');
                customDateRange.classList.add('md:col-span-2');
            } else {
                customDateRange.classList.add('hidden');
                customDateRange.classList.remove('md:col-span-2');

                // Set dates based on period
                switch(this.value) {
                    case 'today':
                        startDateInput.value = today.toISOString().split('T')[0];
                        endDateInput.value = today.toISOString().split('T')[0];
                        break;
                    case 'week':
                        const oneWeekAgo = new Date();
                        oneWeekAgo.setDate(today.getDate() - 7);
                        startDateInput.value = oneWeekAgo.toISOString().split('T')[0];
                        endDateInput.value = today.toISOString().split('T')[0];
                        break;
                    case 'month':
                        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                        startDateInput.value = firstDay.toISOString().split('T')[0];
                        endDateInput.value = today.toISOString().split('T')[0];
                        break;
                    case 'quarter':
                        const quarter = Math.floor(today.getMonth() / 3);
                        const quarterStart = new Date(today.getFullYear(), quarter * 3, 1);
                        startDateInput.value = quarterStart.toISOString().split('T')[0];
                        endDateInput.value = today.toISOString().split('T')[0];
                        break;
                    case 'year':
                        const yearStart = new Date(today.getFullYear(), 0, 1);
                        startDateInput.value = yearStart.toISOString().split('T')[0];
                        endDateInput.value = today.toISOString().split('T')[0];
                        break;
                }
            }
        });

        // Initialize based on current selection
        if (periodSelect.value === 'custom') {
            customDateRange.classList.remove('hidden');
            customDateRange.classList.add('md:col-span-2');
        }

        // Handle form submission
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            // Validate custom dates
            if (periodSelect.value === 'custom') {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);

                if (startDate > endDate) {
                    e.preventDefault();
                    alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
                    return false;
                }
            }

            // Add loading indicator
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memuat...';
            submitBtn.disabled = true;

            // Allow form to submit normally
            return true;
        });

        // Set max date for end date to today
        const today = new Date().toISOString().split('T')[0];
        endDateInput.max = today;
        startDateInput.max = today;
    });

    function printReport() {
        // Prepare data for print
        const printContent = document.querySelector('.print-only');
        const originalContent = document.getElementById('reportContent');

        // Show print content
        printContent.classList.remove('hidden');
        originalContent.classList.add('hidden');

        // Trigger print
        window.print();

        // Restore original content
        setTimeout(() => {
            printContent.classList.add('hidden');
            originalContent.classList.remove('hidden');
        }, 100);
    }

    async function exportToPDF() {
        try {
            showLoading('Membuat PDF...');

            // Get current filter values
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            // Fetch data from the export endpoint
            const response = await fetch(`/reports/export?start_date=${startDate}&end_date=${endDate}&type=pdf`);
            const data = await response.json();

            if (!data.success) {
                throw new Error('Gagal mengambil data laporan');
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');

            // Add logo/header
            doc.setFontSize(20);
            doc.setTextColor(41, 128, 185);
            doc.text('ANANDA LAUNDRY', 105, 15, { align: 'center' });

            doc.setFontSize(16);
            doc.setTextColor(0, 0, 0);
            doc.text('LAPORAN KEUANGAN', 105, 25, { align: 'center' });

            // Periode
            doc.setFontSize(11);
            const periodText = `${new Date(startDate).toLocaleDateString('id-ID')} - ${new Date(endDate).toLocaleDateString('id-ID')}`;
            const dateText = new Date().toLocaleString('id-ID');
            doc.text(`Periode: ${periodText}`, 105, 35, { align: 'center' });
            doc.text(`Tanggal Cetak: ${dateText}`, 105, 40, { align: 'center' });

            // Summary Table
            const summaryData = [
                ['Total Pendapatan', formatCurrency(data.data.summary.total_revenue)],
                ['Total Transaksi', data.data.summary.total_transactions],
                ['Rata-rata Transaksi', formatCurrency(data.data.summary.avg_transaction)],
                ['Biaya Operasional', formatCurrency(data.data.summary.operational_costs)],
                ['Profit Bersih', formatCurrency(data.data.summary.net_profit)],
                ['Profit Margin', data.data.summary.profit_margin.toFixed(1) + '%']
            ];

            doc.autoTable({
                startY: 50,
                head: [['ITEM', 'NILAI']],
                body: summaryData,
                theme: 'grid',
                headStyles: {
                    fillColor: [41, 128, 185],
                    textColor: 255,
                    fontSize: 11
                },
                bodyStyles: { fontSize: 10 },
                columnStyles: {
                    0: { fontStyle: 'bold', cellWidth: 70 },
                    1: { halign: 'right', cellWidth: 70 }
                },
                margin: { left: 20, right: 20 }
            });

            // Transaction details if available
            if (data.data.transactions && data.data.transactions.length > 0) {
                let finalY = doc.lastAutoTable.finalY + 15;
                doc.setFontSize(12);
                doc.text('DETAIL TRANSAKSI', 20, finalY);

                const transactionData = data.data.transactions.map(transaction => [
                    transaction.invoice_number,
                    transaction.customer.name.substring(0, 20),
                    transaction.service.name.substring(0, 15),
                    formatCurrency(transaction.total_amount),
                    new Date(transaction.transaction_date).toLocaleDateString('id-ID')
                ]);

                doc.autoTable({
                    startY: finalY + 5,
                    head: [['Invoice', 'Pelanggan', 'Layanan', 'Total', 'Tanggal']],
                    body: transactionData,
                    theme: 'grid',
                    headStyles: {
                        fillColor: [52, 152, 219],
                        textColor: 255,
                        fontSize: 9
                    },
                    bodyStyles: { fontSize: 8 },
                    columnStyles: {
                        0: { cellWidth: 25 },
                        1: { cellWidth: 40 },
                        2: { cellWidth: 30 },
                        3: { cellWidth: 30, halign: 'right' },
                        4: { cellWidth: 25 }
                    },
                    margin: { left: 20, right: 20 },
                    pageBreak: 'auto'
                });
            }

            // Footer
            const pageCount = doc.internal.getNumberOfPages();
            for(let i = 1; i <= pageCount; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.text(`Halaman ${i} dari ${pageCount}`, 105, 285, { align: 'center' });
                doc.text('Dicetak dari Sistem Ananda Laundry', 105, 290, { align: 'center' });
            }

            // Save PDF
            const fileName = `Laporan_Keuangan_Ananda_Laundry_${new Date().toISOString().split('T')[0]}.pdf`;
            doc.save(fileName);

            hideLoading();
            showSuccess('PDF berhasil diunduh!');
        } catch (error) {
            console.error('PDF Export Error:', error);
            hideLoading();
            showError('Gagal membuat PDF. Silakan coba lagi.');
        }
    }

    async function exportToExcel() {
        try {
            showLoading('Membuat Excel...');

            // Get current filter values
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            // Fetch data from the export endpoint
            const response = await fetch(`/reports/export?start_date=${startDate}&end_date=${endDate}&type=excel`);
            const data = await response.json();

            if (!data.success) {
                throw new Error('Gagal mengambil data laporan');
            }

            // Create workbook
            const workbook = XLSX.utils.book_new();

            // Summary sheet
            const summaryData = [
                ['LAPORAN KEUANGAN - ANANDA LAUNDRY'],
                [`Periode: ${new Date(startDate).toLocaleDateString('id-ID')} - ${new Date(endDate).toLocaleDateString('id-ID')}`],
                [`Tanggal Cetak: ${new Date().toLocaleString('id-ID')}`],
                [],
                ['ITEM', 'NILAI'],
                ['Total Pendapatan', data.data.summary.total_revenue],
                ['Total Transaksi', data.data.summary.total_transactions],
                ['Rata-rata Transaksi', data.data.summary.avg_transaction],
                ['Biaya Operasional', data.data.summary.operational_costs],
                ['Profit Bersih', data.data.summary.net_profit],
                ['Profit Margin (%)', data.data.summary.profit_margin],
                ['Biaya Operasional/Revenue (%)', data.data.summary.operational_costs / data.data.summary.total_revenue * 100],
                [],
                ['ANALISIS'],
                ['Keterangan', 'Nilai'],
                ['Status Profit Margin',
                    data.data.summary.profit_margin >= 30 ? 'Excellent (> 30%)' :
                    data.data.summary.profit_margin >= 20 ? 'Baik (20-30%)' :
                    data.data.summary.profit_margin >= 10 ? 'Cukup (10-20%)' : 'Perlu Perbaikan (< 10%)'
                ],
                ['Status Biaya Operasional',
                    data.data.summary.operational_costs / data.data.summary.total_revenue * 100 <= 60 ?
                    'Efisien (≤ 60%)' : 'Perlu Efisiensi (> 60%)'
                ]
            ];

            const wsSummary = XLSX.utils.aoa_to_sheet(summaryData);

            // Apply styles and column widths
            wsSummary['!cols'] = [
                { wch: 30 }, // Item column
                { wch: 20 }  // Value column
            ];

            // Merge cells for title
            wsSummary['!merges'] = [
                { s: { r: 0, c: 0 }, e: { r: 0, c: 1 } },
                { s: { r: 1, c: 0 }, e: { r: 1, c: 1 } },
                { s: { r: 2, c: 0 }, e: { r: 2, c: 1 } }
            ];

            XLSX.utils.book_append_sheet(workbook, wsSummary, "Ringkasan");

            // Transactions sheet
            if (data.data.transactions && data.data.transactions.length > 0) {
                const transactionHeaders = [['No', 'Invoice', 'Pelanggan', 'Telepon', 'Layanan', 'Berat (kg)', 'Total', 'Tanggal', 'Status']];
                const transactionData = data.data.transactions.map((transaction, index) => [
                    index + 1,
                    transaction.invoice_number,
                    transaction.customer.name,
                    transaction.customer.phone,
                    transaction.service.name,
                    transaction.quantity,
                    transaction.total_amount,
                    new Date(transaction.transaction_date).toISOString().split('T')[0],
                    transaction.payment_status === 'paid' ? 'LUNAS' : 'BELUM'
                ]);

                const wsTransactions = XLSX.utils.aoa_to_sheet([...transactionHeaders, ...transactionData]);
                wsTransactions['!cols'] = [
                    { wch: 5 },  // No
                    { wch: 15 }, // Invoice
                    { wch: 25 }, // Pelanggan
                    { wch: 15 }, // Telepon
                    { wch: 20 }, // Layanan
                    { wch: 10 }, // Berat
                    { wch: 15 }, // Total
                    { wch: 12 }, // Tanggal
                    { wch: 10 }  // Status
                ];

                // Add total row
                const totalRow = ['TOTAL', '', '', '', '', '', data.data.summary.total_revenue, '', ''];
                XLSX.utils.sheet_add_aoa(wsTransactions, [totalRow], { origin: -1 });

                XLSX.utils.book_append_sheet(workbook, wsTransactions, "Transaksi");
            }

            // Save file
            const fileName = `Laporan_Keuangan_Ananda_Laundry_${new Date().toISOString().split('T')[0].replace(/-/g, '')}.xlsx`;
            XLSX.writeFile(workbook, fileName);

            hideLoading();
            showSuccess('Excel berhasil diunduh!');
        } catch (error) {
            console.error('Excel Export Error:', error);
            hideLoading();
            showError('Gagal membuat Excel. Silakan coba lagi.');
        }
    }

    function formatCurrency(number) {
        return 'Rp ' + number.toLocaleString('id-ID');
    }

    // Helper functions for loading and notifications
    function showLoading(message = 'Memproses...') {
        // Create loading overlay
        const overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            color: white;
            font-size: 18px;
        `;

        overlay.innerHTML = `
            <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-white mb-4"></div>
            <div>${message}</div>
        `;

        document.body.appendChild(overlay);
    }

    function hideLoading() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.remove();
        }
    }

    function showSuccess(message) {
        // Create notification
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            animation: slideIn 0.3s ease-out;
        `;

        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(notification);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    function showError(message) {
        // Create notification
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ef4444;
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            animation: slideIn 0.3s ease-out;
        `;

        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-exclamation-circle" style="font-size: 20px;"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(notification);

        // Remove after 5 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }

    // Add CSS for animations
    if (!document.getElementById('animation-styles')) {
        const style = document.createElement('style');
        style.id = 'animation-styles';
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
            .animate-spin {
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    }
</script>
@endpush

@endsection
