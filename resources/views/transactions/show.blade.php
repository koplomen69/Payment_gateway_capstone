@extends('layouts.app')

@section('title', 'Detail Transaksi')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Detail Transaksi</h2>
                <p class="text-gray-600">Invoice: {{ $transaction->invoice_number }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('transactions.edit', $transaction) }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-edit"></i>
                    <span>Edit</span>
                </a>
                <a href="{{ route('transactions.index') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Transaction Details -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Informasi Transaksi</h3>

                <div class="flex justify-between">
                    <span class="text-gray-600">Tanggal:</span>
                    <span class="font-medium">{{ $transaction->transaction_date->format('d/m/Y') }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Invoice:</span>
                    <span class="font-medium">{{ $transaction->invoice_number }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span class="font-medium px-2 py-1 rounded-full
                        {{ $transaction->payment_status == 'paid' ? 'bg-green-100 text-green-800' :
                           ($transaction->payment_status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ $transaction->payment_status }}
                    </span>
                </div>
            </div>

            <!-- Customer Details -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Informasi Pelanggan</h3>

                <div class="flex justify-between">
                    <span class="text-gray-600">Nama:</span>
                    <span class="font-medium">{{ $transaction->customer->name }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Telepon:</span>
                    <span class="font-medium">{{ $transaction->customer->phone ?? '-' }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Alamat:</span>
                    <span class="font-medium text-right">{{ $transaction->customer->address ?? '-' }}</span>
                </div>
            </div>

            <!-- Service Details -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Detail Layanan</h3>

                <div class="flex justify-between">
                    <span class="text-gray-600">Layanan:</span>
                    <span class="font-medium">{{ $transaction->service->name }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Jumlah:</span>
                    <span class="font-medium">{{ $transaction->quantity }} {{ $transaction->service->unit }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Harga Satuan:</span>
                    <span class="font-medium">Rp {{ number_format($transaction->price, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Informasi Pembayaran</h3>

                <div class="flex justify-between">
                    <span class="text-gray-600">Metode:</span>
                    <span class="font-medium">{{ $transaction->payment_method }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Total:</span>
                    <span class="font-bold text-green-600 text-lg">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($transaction->notes)
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <h4 class="font-semibold text-gray-800 mb-2">Catatan:</h4>
            <p class="text-gray-600">{{ $transaction->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
