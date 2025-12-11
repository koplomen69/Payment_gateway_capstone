@extends('layouts.app')

@section('title', 'Detail Pelanggan - Ananda Laundry')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Detail Pelanggan</h1>
                <p class="text-gray-600 mt-2">Informasi lengkap pelanggan: {{ $customer->name }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('customers.edit', $customer) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg flex items-center space-x-2 transition duration-200 shadow-md">
                    <i class="fas fa-edit"></i>
                    <span>Edit Data</span>
                </a>
                <a href="{{ route('customers.index') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2.5 rounded-lg flex items-center space-x-2 transition duration-200 shadow-md">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Customer Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Customer Info Card -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-blue-600 text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">{{ $customer->name }}</h2>
                        <p class="text-gray-600">Pelanggan Ananda Laundry</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Nomor Telepon</label>
                            <div class="flex items-center">
                                <i class="fas fa-phone text-gray-400 mr-2"></i>
                                <span class="text-lg font-medium text-gray-800">
                                    {{ $customer->phone ? '+62' . $customer->phone : '-' }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Alamat</label>
                            <div class="flex items-start">
                                <i class="fas fa-map-marker-alt text-gray-400 mr-2 mt-1"></i>
                                <span class="text-gray-800">
                                    {{ $customer->address ?? 'Alamat belum diisi' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                            <div class="flex items-center">
                                <i class="fas fa-circle text-green-500 mr-2"></i>
                                <span class="text-green-600 font-medium">Aktif</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Terdaftar Sejak</label>
                            <div class="flex items-center">
                                <i class="fas fa-calendar-alt text-gray-400 mr-2"></i>
                                <span class="text-gray-800">
                                    {{ $customer->created_at->format('d F Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($customer->notes)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                        <i class="fas fa-sticky-note text-blue-500 mr-2"></i>
                        Catatan Khusus
                    </h4>
                    <p class="text-gray-700 bg-gray-50 p-4 rounded-lg">{{ $customer->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Stats Card -->
        <div>
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-6">Statistik</h3>

                <div class="space-y-6">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-blue-600 mb-2">{{ $customer->transactions_count ?? 0 }}</div>
                        <p class="text-sm text-gray-600">Total Transaksi</p>
                    </div>

                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600 mb-2">
                            Rp {{ number_format($customer->total_spent ?? 0, 0, ',', '.') }}
                        </div>
                        <p class="text-sm text-gray-600">Total Belanja</p>
                    </div>

                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600 mb-2">
                            Rp {{ number_format(($customer->transactions_count ?? 0) > 0 ? ($customer->total_spent ?? 0) / ($customer->transactions_count ?? 1) : 0, 0, ',', '.') }}
                        </div>
                        <p class="text-sm text-gray-600">Rata-rata per Transaksi</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-history text-blue-500 mr-2"></i>
                Riwayat Transaksi Terbaru
            </h3>
            <a href="{{ route('transactions.create') }}?customer_id={{ $customer->id }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
                <i class="fas fa-plus"></i>
                <span>Transaksi Baru</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Layanan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($customer->transactions->take(5) as $transaction)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $transaction->invoice_number }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $transaction->transaction_date->format('d/m/Y') }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-900">{{ $transaction->service->name }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm font-bold text-green-600">
                                Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                {{ $transaction->payment_status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <a href="{{ route('transactions.show', $transaction) }}"
                               class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                Lihat <i class="fas fa-external-link-alt ml-1"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-receipt text-3xl mb-3 block text-gray-300"></i>
                            Belum ada transaksi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customer->transactions->count() > 5)
        <div class="mt-4 text-center">
            <a href="{{ route('transactions.index') }}?customer={{ $customer->id }}"
               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                Lihat semua transaksi <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
