<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Ananda Laundry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-tshirt text-2xl"></i>
                    <a href="{{ route('dashboard') }}" class="text-2xl font-bold">Ananda Laundry</a>
                </div>

                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('dashboard') }}" class="hover:text-blue-200 transition duration-200 {{ request()->routeIs('dashboard') ? 'font-bold' : '' }}">
                        <i class="fas fa-home mr-1"></i>Dashboard
                    </a>
                    <a href="{{ route('transactions.index') }}" class="hover:text-blue-200 transition duration-200 {{ request()->routeIs('transactions.*') ? 'font-bold' : '' }}">
                        <i class="fas fa-receipt mr-1"></i>Transaksi
                    </a>
                    <a href="{{ route('customers.index') }}" class="hover:text-blue-200 transition duration-200 {{ request()->routeIs('customers.*') ? 'font-bold' : '' }}">
                        <i class="fas fa-users mr-1"></i>Pelanggan
                    </a>
                    <a href="{{ route('services.index') }}" class="hover:text-blue-200 transition duration-200 {{ request()->routeIs('services.*') ? 'font-bold' : '' }}">
                        <i class="fas fa-concierge-bell mr-1"></i>Layanan
                    </a>
                    <a href="{{ route('reports.index') }}" class="hover:text-blue-200 transition duration-200 {{ request()->routeIs('reports.*') ? 'font-bold' : '' }}">
                        <i class="fas fa-chart-bar mr-1"></i>Laporan
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-white">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="md:hidden hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <a href="{{ route('dashboard') }}" class="hover:text-blue-200 transition duration-200 {{ request()->routeIs('dashboard') ? 'font-bold' : '' }}">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                    <a href="{{ route('transactions.index') }}" class="hover:text-blue-200 transition duration-200 {{ request()->routeIs('transactions.*') ? 'font-bold' : '' }}">
                        <i class="fas fa-receipt mr-2"></i>Transaksi
                    </a>
                    <a href="{{ route('customers.index') }}" class="hover:text-blue-200 transition duration-200 {{ request()->routeIs('customers.*') ? 'font-bold' : '' }}">
                        <i class="fas fa-users mr-2"></i>Pelanggan
                    </a>
                    <a href="{{ route('services.index') }}" class="hover:text-blue-200 transition duration-200 {{ request()->routeIs('services.*') ? 'font-bold' : '' }}">
                        <i class="fas fa-concierge-bell mr-2"></i>Layanan
                    </a>
                    <a href="{{ route('reports.index') }}" class="hover:text-blue-200 transition duration-200 {{ request()->routeIs('reports.*') ? 'font-bold' : '' }}">
                        <i class="fas fa-chart-bar mr-2"></i>Laporan
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">@yield('title')</h1>
            <p class="text-gray-600 mt-2">@yield('subtitle', 'Sistem Informasi Keuangan Ananda Laundry')</p>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-info-circle mr-2"></i>{{ session('info') }}
            </div>
        @endif

        <!-- Content -->
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; {{ date('Y') }} Ananda Laundry Financial System. All rights reserved.</p>
            <p class="mt-2 text-gray-400">UMKM Ananda Laundry - Wonokromo, Surabaya</p>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>
</body>
</html>
