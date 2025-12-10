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

                <!-- Tombol-tombol aksi -->
                <div class="flex flex-wrap gap-3">
                    <!-- Tombol Edit -->
                    <a href="{{ route('transactions.edit', $transaction) }}"
                        class="bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-lg flex items-center space-x-2 shadow-md">
                        <i class="fas fa-edit"></i>
                        <span>Edit</span>
                    </a>

                    <!-- Tombol Cek Status (untuk pembayaran pending Midtrans) -->
                    @if($transaction->payment_method === 'midtrans' && $transaction->payment_status === 'pending')
                        <button onclick="checkPaymentStatus({{ $transaction->id }})"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg flex items-center space-x-2 shadow-md">
                            <i class="fas fa-sync-alt"></i>
                            <span id="check-status-btn">Cek Status Pembayaran</span>
                        </button>
                    @endif

                    <!-- Tombol Kirim Ulang WhatsApp (hanya muncul kalau sudah PAID & ada nomor HP) -->
                    @if ($transaction->payment_status === 'paid' && $transaction->customer->phone)
                        <form action="{{ route('transactions.resend-whatsapp', $transaction) }}" method="POST"
                            class="inline">
                            @csrf
                            <button type="submit"
                                onclick="return confirm('Kirim ulang resi ke WhatsApp {{ $transaction->customer->phone }}?')"
                                class="bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700
                               text-white font-medium px-6 py-3 rounded-lg flex items-center space-x-2 shadow-lg
                               transition transform hover:scale-105">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966 1.108.966.149 0-.198.198-.297.297-.149.273-.099.471.149.471.297 0 .198-.099.396.099.297.197.099.395.099.593 0 .198-.099.396-.198.594-.099.198-.099.297 0 .297.099.198.396.297.593.198.198.099.396 0 .594-.099.198-.099.297-.198.297-.396.099-.198.198-.396.099-.594-.099-.198-.297-.297-.495-.297z" />
                                    <path
                                        d="M12 2C6.48 2 2 6.48 2 12c0 2.136.672 4.126 1.818 5.764L2 22l4.318-1.818C7.936 21.328 9.936 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm0 18c-1.805 0-3.47-.54-4.864-1.454l-.346-.21-3.09 1.09 1.09-3.09-.21-.346C3.54 15.47 3 13.805 3 12c0-4.963 4.037-9 9-9s9 4.037 9 9-4.037 9-9 9z" />
                                </svg>
                                <span>Kirim Ulang Resi WA</span>
                            </button>
                        </form>
                    @endif

                    <!-- Tombol Kembali -->
                    <a href="{{ route('transactions.index') }}"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg flex items-center space-x-2 shadow-md">
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
                        <span id="payment-status"
                            class="font-medium px-2 py-1 rounded-full
                        {{ $transaction->payment_status == 'paid'
                            ? 'bg-green-100 text-green-800'
                            : ($transaction->payment_status == 'pending'
                                ? 'bg-yellow-100 text-yellow-800'
                                : 'bg-red-100 text-red-800') }}">
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
                        <span class="font-bold text-green-600 text-lg">Rp
                            {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if ($transaction->notes)
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-2">Catatan:</h4>
                    <p class="text-gray-600">{{ $transaction->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        async function checkPaymentStatus(transactionId) {
            const btn = document.getElementById('check-status-btn');
            const statusBadge = document.getElementById('payment-status');

            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengecek...';
            btn.disabled = true;

            try {
                const response = await fetch(`/transactions/${transactionId}/check-status`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Update status badge
                    statusBadge.textContent = data.payment_status;
                    statusBadge.className = 'font-medium px-2 py-1 rounded-full ' +
                        (data.payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800');

                    // Refresh halaman jika status berubah jadi paid
                    if (data.payment_status === 'paid') {
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }

                    alert('✅ Status pembayaran berhasil diperbarui: ' + data.payment_status);
                } else {
                    alert('⚠️ ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('❌ Gagal mengecek status: ' + error.message);
            } finally {
                btn.innerHTML = '<i class="fas fa-sync-alt"></i> Cek Status Pembayaran';
                btn.disabled = false;
            }
        }

        // Auto-check status setiap 30 detik jika masih pending
        @if($transaction->payment_method === 'midtrans' && $transaction->payment_status === 'pending')
            let checkInterval = setInterval(() => {
                checkPaymentStatus({{ $transaction->id }});
            }, 30000); // 30 detik

            // Stop interval jika status berubah
            document.getElementById('check-status-btn')?.addEventListener('click', function() {
                if (this.textContent.includes('Cek')) {
                    clearInterval(checkInterval);
                }
            });
        @endif
    </script>
@endsection
