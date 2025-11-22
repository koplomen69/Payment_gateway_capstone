<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - {{ $transaction->midtrans_order_id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Snap.js dari Midtrans -->
    <script type="text/javascript"
            src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="{{ config('midtrans.client_key') }}"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Pembayaran Laundry</h1>
                <p class="text-gray-600">Selesaikan pembayaran Anda</p>
            </div>

            <!-- Transaction Info -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-gray-600">Order ID:</span>
                    <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">{{ $transaction->midtrans_order_id }}</span>
                </div>
                <div class="flex justify-between items-center mb-4">
                    <span class="text-gray-600">Total:</span>
                    <span class="text-xl font-bold text-green-600">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Layanan:</span>
                    <span class="text-right font-medium">{{ $transaction->service->name }}</span>
                </div>
            </div>

            <!-- Payment Button -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <button id="pay-button"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 px-6 rounded-lg font-semibold text-lg transition duration-200 flex items-center justify-center space-x-2">
                    <i class="fas fa-credit-card"></i>
                    <span>Bayar Sekarang</span>
                </button>

                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600">Anda akan diarahkan ke halaman pembayaran Midtrans</p>
                </div>
            </div>

            <!-- Back Button -->
            <div class="text-center mt-6">
                <a href="{{ route('transactions.index') }}"
                   class="text-blue-600 hover:text-blue-800 font-medium">
                    ← Kembali ke Daftar Transaksi
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const payButton = document.getElementById('pay-button');
            const snapToken = '{{ $transaction->midtrans_snap_token }}';

            console.log('Snap Token:', snapToken);

            if (!snapToken) {
                alert('Error: Token pembayaran tidak tersedia. Silakan hubungi admin.');
                window.location.href = '{{ route("transactions.index") }}';
                return;
            }

            payButton.addEventListener('click', function() {
                // Disable button untuk prevent multiple clicks
                payButton.disabled = true;
                payButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Memuat...</span>';

                // Pastikan snap.js sudah terload
                if (typeof window.snap === 'undefined') {
                    alert('Error: Payment gateway belum siap. Silakan refresh halaman.');
                    payButton.disabled = false;
                    payButton.innerHTML = '<i class="fas fa-credit-card"></i><span>Bayar Sekarang</span>';
                    return;
                }

                // Buka popup pembayaran
                window.snap.pay(snapToken, {
                    onSuccess: function(result) {
                        console.log('Payment Success:', result);
                        alert('Pembayaran berhasil! Terima kasih.');
                        window.location.href = '{{ route("transactions.show", $transaction) }}';
                    },
                    onPending: function(result) {
                        console.log('Payment Pending:', result);
                        alert('Pembayaran Anda sedang diproses. Silakan tunggu konfirmasi.');
                        window.location.href = '{{ route("transactions.show", $transaction) }}';
                    },
                    onError: function(result) {
                        console.log('Payment Error:', result);
                        alert('Pembayaran gagal. Silakan coba lagi atau gunakan metode pembayaran lain.');
                    },
                    onClose: function() {
                        console.log('Payment Popup Closed');
                        payButton.disabled = false;
                        payButton.innerHTML = '<i class="fas fa-credit-card"></i><span>Bayar Sekarang</span>';
                    }
                });
            });

            // Auto-click setelah 2 detik (optional)
            setTimeout(function() {
                console.log('Auto-clicking pay button...');
                payButton.click();
            }, 2000);
        });
    </script>

    <!-- Font Awesome untuk icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>
</html>
