@extends('layouts.app')

@section('title', 'Laporan Profit & Loss - Ananda Laundry')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Laporan Profit & Loss</h1>
                <p class="text-gray-600 mt-2">Analisis keuntungan dan kerugian bisnis Ananda Laundry</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button onclick="printReport()"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg flex items-center space-x-2 transition duration-200 shadow-md">
                    <i class="fas fa-print"></i>
                    <span>Cetak Laporan</span>
                </button>
                <a href="{{ route('reports.index') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2.5 rounded-lg flex items-center space-x-2 transition duration-200 shadow-md">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
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
        <!-- Profit & Loss Analysis -->
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- P&L Statement -->
                <div class="lg:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Laporan Laba Rugi</h3>
                    <div class="space-y-3">
                        <!-- Revenue -->
                        <div class="flex justify-between items-center p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                            <div>
                                <span class="font-medium text-gray-800">Pendapatan Kotor</span>
                                <p class="text-xs text-gray-500">Gross Revenue</p>
                            </div>
                            <span class="font-bold text-blue-600 text-xl">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                        </div>

                        <!-- Operational Costs -->
                        <div class="flex justify-between items-center p-4 bg-yellow-50 rounded-lg border-l-4 border-yellow-500">
                            <div>
                                <span class="font-medium text-gray-800">Biaya Operasional (60%)</span>
                                <p class="text-xs text-gray-500">Operating Expenses</p>
                            </div>
                            <span class="font-bold text-yellow-600 text-xl">Rp {{ number_format($operationalCosts, 0, ',', '.') }}</span>
                        </div>

                        <!-- Divider -->
                        <div class="border-t-2 border-gray-300"></div>

                        <!-- Net Profit -->
                        <div class="flex justify-between items-center p-4 bg-green-50 rounded-lg border-2 border-green-300">
                            <div>
                                <span class="font-medium text-gray-800 text-lg">Laba Bersih</span>
                                <p class="text-xs text-gray-500">Net Profit</p>
                            </div>
                            <span class="font-bold text-green-600 text-2xl">Rp {{ number_format($netProfit, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Key Metrics -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Metrik Kunci</h3>
                    <div class="space-y-4">
                        <!-- Profit Margin -->
                        <div class="p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700">Profit Margin</span>
                                <span class="text-lg font-bold text-indigo-600">
                                    {{ $totalRevenue > 0 ? number_format(($netProfit / $totalRevenue) * 100, 1) : 0 }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-indigo-500 h-2 rounded-full"
                                     style="width: {{ $totalRevenue > 0 ? min(($netProfit / $totalRevenue) * 100, 100) : 0 }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Target: ≥ 30% (Excellent)</p>
                        </div>

                        <!-- Operating Ratio -->
                        <div class="p-4 bg-orange-50 rounded-lg border border-orange-200">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700">Operating Ratio</span>
                                <span class="text-lg font-bold text-orange-600">
                                    {{ $totalRevenue > 0 ? number_format(($operationalCosts / $totalRevenue) * 100, 1) : 0 }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-orange-500 h-2 rounded-full"
                                     style="width: {{ $totalRevenue > 0 ? min(($operationalCosts / $totalRevenue) * 100, 100) : 0 }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Target: ≤ 60% (Efficient)</p>
                        </div>

                        <!-- Transaction Count -->
                        <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                            <p class="text-sm text-gray-700 mb-1">Total Transaksi</p>
                            <p class="text-2xl font-bold text-green-600">{{ $totalTransactions }}</p>
                        </div>

                        <!-- Average Transaction -->
                        <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                            <p class="text-sm text-gray-700 mb-1">Avg. Transaksi</p>
                            <p class="text-2xl font-bold text-purple-600">
                                Rp {{ number_format($totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Analysis -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Status Assessment -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-comment-dots text-blue-500 mr-2"></i>
                    Evaluasi Performa
                </h3>

                @php
                    $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;
                    $operatingRatio = $totalRevenue > 0 ? ($operationalCosts / $totalRevenue) * 100 : 0;
                @endphp

                <div class="space-y-4">
                    <!-- Profit Margin Assessment -->
                    <div class="p-4 rounded-lg @if($profitMargin >= 30) bg-green-50 border-l-4 border-green-500 @elseif($profitMargin >= 20) bg-yellow-50 border-l-4 border-yellow-500 @else bg-red-50 border-l-4 border-red-500 @endif">
                        <h4 class="font-semibold @if($profitMargin >= 30) text-green-800 @elseif($profitMargin >= 20) text-yellow-800 @else text-red-800 @endif mb-1">
                            @if($profitMargin >= 30)
                            ✅ Profit Margin Excellent
                            @elseif($profitMargin >= 20)
                            ✓ Profit Margin Baik
                            @else
                            ⚠️ Profit Margin Rendah
                            @endif
                        </h4>
                        <p class="text-sm @if($profitMargin >= 30) text-green-700 @elseif($profitMargin >= 20) text-yellow-700 @else text-red-700 @endif">
                            @if($profitMargin >= 30)
                            Margin sebesar {{ number_format($profitMargin, 1) }}% menunjukkan bisnis sangat sehat dan menguntungkan.
                            @elseif($profitMargin >= 20)
                            Margin sebesar {{ number_format($profitMargin, 1) }}% menunjukkan performa yang baik.
                            @else
                            Margin sebesar {{ number_format($profitMargin, 1) }}% masih di bawah target optimal. Pertimbangkan meningkatkan revenue atau mengurangi biaya.
                            @endif
                        </p>
                    </div>

                    <!-- Operating Cost Assessment -->
                    <div class="p-4 rounded-lg @if($operatingRatio <= 60) bg-green-50 border-l-4 border-green-500 @else bg-red-50 border-l-4 border-red-500 @endif">
                        <h4 class="font-semibold @if($operatingRatio <= 60) text-green-800 @else text-red-800 @endif mb-1">
                            @if($operatingRatio <= 60)
                            ✅ Biaya Operasional Efisien
                            @else
                            ⚠️ Biaya Operasional Tinggi
                            @endif
                        </h4>
                        <p class="text-sm @if($operatingRatio <= 60) text-green-700 @else text-red-700 @endif">
                            @if($operatingRatio <= 60)
                            Biaya operasional {{ number_format($operatingRatio, 1) }}% dari revenue menunjukkan operasional yang efisien.
                            @else
                            Biaya operasional {{ number_format($operatingRatio, 1) }}% dari revenue sudah melebihi target 60%. Perlu analisis dan optimisasi lebih lanjut.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Recommendations -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                    Rekomendasi
                </h3>

                <div class="space-y-3">
                    @if($totalRevenue > 0)
                        <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <p class="text-sm text-blue-800">
                                <span class="font-semibold">📊 Data Available:</span> Analisis berdasarkan {{ $totalTransactions }} transaksi dengan revenue Rp {{ number_format($totalRevenue, 0, ',', '.') }}.
                            </p>
                        </div>

                        @if($profitMargin < 20)
                        <div class="p-3 bg-orange-50 rounded-lg border border-orange-200">
                            <p class="text-sm text-orange-800">
                                <span class="font-semibold">🎯 Strategi:</span> Fokus pada peningkatan revenue melalui promosi layanan atau penambahan paket layanan baru.
                            </p>
                        </div>
                        @endif

                        @if($operatingRatio > 60)
                        <div class="p-3 bg-orange-50 rounded-lg border border-orange-200">
                            <p class="text-sm text-orange-800">
                                <span class="font-semibold">💡 Optimisasi Biaya:</span> Evaluasi ulang struktur biaya operasional. Cari cara untuk efisiensi tanpa mengorbankan kualitas layanan.
                            </p>
                        </div>
                        @endif

                        <div class="p-3 bg-green-50 rounded-lg border border-green-200">
                            <p class="text-sm text-green-800">
                                <span class="font-semibold">✅ Tracking:</span> Monitor performa secara berkala setiap minggu atau bulan untuk trend dan peningkatan.
                            </p>
                        </div>
                    @else
                    <div class="p-4 bg-gray-100 rounded-lg text-center text-gray-600">
                        <i class="fas fa-inbox text-4xl mb-3 block text-gray-400"></i>
                        Belum ada transaksi pada periode ini. Mulai dengan membuat transaksi untuk melihat analisis.
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Detailed Transactions Table -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-receipt text-blue-500 mr-2"></i>
                Daftar Transaksi Detail
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Layanan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Satuan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($transactions as $transaction)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="text-sm font-medium text-gray-900">{{ $transaction->invoice_number }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-900">{{ $transaction->customer->name }}</div>
                                <div class="text-xs text-gray-500">{{ $transaction->customer->phone }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-gray-900">{{ $transaction->service->name }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-gray-900">{{ $transaction->quantity }} {{ $transaction->service->unit }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-gray-900">Rp {{ number_format($transaction->price, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-semibold text-green-600">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-3xl mb-3 block text-gray-300"></i>
                                Tidak ada transaksi pada periode ini
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($transactions->count() > 0)
                    <tfoot>
                        <tr class="bg-gray-50 border-t-2 border-gray-300">
                            <td colspan="5" class="px-4 py-3 text-right font-bold text-gray-800">TOTAL:</td>
                            <td class="px-4 py-3 font-bold text-green-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
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

            return true;
        });

        // Set max date for end date to today
        const today = new Date().toISOString().split('T')[0];
        endDateInput.max = today;
        startDateInput.max = today;
    });

    function printReport() {
        window.print();
    }
</script>

<style>
    @media print {
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
        button, a { display: none !important; }
    }
</style>
@endpush

@endsection
