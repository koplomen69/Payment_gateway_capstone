<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Ananda Laundry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation (Updated with Tabs) -->
        <nav class="bg-blue-600 text-white shadow-lg">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <i class="fas fa-tshirt text-2xl"></i>
                        <h1 class="text-2xl font-bold">Ananda Laundry</h1>
                    </div>
                    <div class="flex space-x-6 text-sm">
                        <a href="{{ route('dashboard') }}" class="hover:text-blue-200">Dashboard</a>
                        <a href="{{ route('transactions.index') }}" class="hover:text-blue-200">Transaksi</a>
                        <a href="{{ route('transactions.index') }}?payment_status=pending" class="hover:text-blue-200">Pembayaran</a> <!-- Tab baru untuk akses page payment (filter pending) -->
                        <a href="{{ route('services.index') }}" class="hover:text-blue-200">Layanan</a>
                        <a href="{{ route('customers.index') }}" class="hover:text-blue-200">Pelanggan</a>
                        <a href="{{ route('reports.index') }}" class="hover:text-blue-200">Laporan</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="container mx-auto px-4 py-8">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800">Dashboard</h2>
                <p class="text-gray-600">Selamat datang di Sistem Informasi Keuangan Ananda Laundry</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Revenue -->
                <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Pendapatan</p>
                            <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Transactions -->
                <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Transaksi</p>
                            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalTransactions) }}</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-receipt text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Customers -->
                <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Pelanggan</p>
                            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalCustomers) }}</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full">
                            <i class="fas fa-users text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Services -->
                <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Layanan Tersedia</p>
                            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalServices) }}</p>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-full">
                            <i class="fas fa-concierge-bell text-orange-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions & Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Recent Transactions (Updated with Aksi column for payment) -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold text-gray-800">Transaksi Terbaru</h3>
                            <a href="{{ route('transactions.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Lihat Semua
                            </a>
                        </div>

                        @if($recentTransactions->count() > 0)
                            <div class="space-y-4">
                                @foreach($recentTransactions as $transaction)
                                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                    <div class="flex items-center space-x-4 flex-grow">
                                        <div class="bg-gray-100 p-2 rounded-full">
                                            <i class="fas fa-receipt text-gray-600"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-800">{{ $transaction->invoice_number }}</p>
                                            <p class="text-sm text-gray-600">{{ $transaction->customer->name }} - {{ $transaction->service->name }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right min-w-[150px]">
                                        <p class="font-bold text-green-600">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
                                        <span class="inline-block px-2 py-1 text-xs rounded-full
                                            {{ $transaction->payment_status == 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($transaction->payment_status) }}
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        @if($transaction->payment_status == 'pending' && $transaction->payment_method == 'midtrans')
                                            <a href="{{ route('transactions.payment', $transaction) }}" class="btn bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm">
                                                Bayar
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <i class="fas fa-receipt text-gray-400 text-4xl mb-4"></i>
                                <p class="text-gray-500">Belum ada transaksi</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions (Updated with Kelola Pembayaran) -->
                <div class="space-y-6">
                    <!-- Quick Actions Card -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Aksi Cepat</h3>
                        <div class="space-y-3">
                            <a href="{{ route('transactions.create') }}"
                               class="flex items-center space-x-3 p-3 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition duration-200">
                                <i class="fas fa-plus-circle"></i>
                                <span>Tambah Transaksi</span>
                            </a>
                            <a href="{{ route('customers.create') }}"
                               class="flex items-center space-x-3 p-3 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition duration-200">
                                <i class="fas fa-user-plus"></i>
                                <span>Tambah Pelanggan</span>
                            </a>
                            <a href="{{ route('services.create') }}"
                               class="flex items-center space-x-3 p-3 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition duration-200">
                                <i class="fas fa-concierge-bell"></i>
                                <span>Tambah Layanan</span>
                            </a>
                            <a href="{{ route('transactions.index') }}?payment_status=pending"
                               class="flex items-center space-x-3 p-3 bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-100 transition duration-200">
                                <i class="fas fa-credit-card"></i>
                                <span>Kelola Pembayaran</span> <!-- Tambahan untuk akses page payment -->
                            </a>
                            <a href="{{ route('reports.index') }}"
                               class="flex items-center space-x-3 p-3 bg-orange-50 text-orange-700 rounded-lg hover:bg-orange-100 transition duration-200">
                                <i class="fas fa-chart-bar"></i>
                                <span>Lihat Laporan</span>
                            </a>
                        </div>
                    </div>

                    <!-- Status Info -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Status Sistem</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Database</span>
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Connected</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Environment</span>
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Local</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Midtrans</span>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Sandbox</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="mt-12 text-center text-gray-500 text-sm">
                <p>&copy; {{ date('Y') }} Ananda Laundry Financial System. All rights reserved.</p>
                <p class="mt-1">UMKM Ananda Laundry - Wonokromo, Surabaya</p>
            </footer>
        </div>
    </div>
</body>
</html>
