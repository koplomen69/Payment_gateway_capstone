@extends('layouts.app')

@section('title', 'Daftar Pelanggan - Ananda Laundry')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Daftar Pelanggan</h1>
                <p class="text-gray-600 mt-2">Kelola data pelanggan Ananda Laundry</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('customers.create') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg flex items-center space-x-2 transition duration-200 shadow-md">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Pelanggan</span>
                </a>
                <a href="{{ route('dashboard') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2.5 rounded-lg flex items-center space-x-2 transition duration-200 shadow-md">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Pelanggan</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $customers->count() }}</p>
                </div>
                <div class="bg-blue-100 p-2 rounded-full">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Aktif Bulan Ini</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ $customers->where('created_at', '>=', now()->subMonth())->count() }}
                    </p>
                </div>
                <div class="bg-green-100 p-2 rounded-full">
                    <i class="fas fa-user-check text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Transaksi</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $customers->sum('transactions_count') }}</p>
                </div>
                <div class="bg-purple-100 p-2 rounded-full">
                    <i class="fas fa-receipt text-purple-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Pelanggan Baru</p>
                    <p class="text-2xl font-bold text-orange-600">
                        {{ $customers->where('created_at', '>=', now()->subWeek())->count() }}
                    </p>
                </div>
                <div class="bg-orange-100 p-2 rounded-full">
                    <i class="fas fa-user-plus text-orange-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-600 mr-3"></i>
            <div>
                <p class="font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text"
                           id="searchInput"
                           placeholder="Cari pelanggan berdasarkan nama, telepon, atau alamat..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="flex space-x-3">
                <select id="sortSelect" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="name_asc">Nama A-Z</option>
                    <option value="name_desc">Nama Z-A</option>
                    <option value="recent">Terbaru</option>
                    <option value="oldest">Terlama</option>
                    <option value="transactions_desc">Transaksi Terbanyak</option>
                </select>

                <button id="resetFilter" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-redo"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full" id="customersTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pelanggan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Transaksi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terdaftar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-blue-600"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $customer->name }}</div>
                                    <div class="text-xs text-gray-500">ID: {{ str_pad($customer->id, 6, '0', STR_PAD_LEFT) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                @if($customer->phone)
                                <div class="flex items-center">
                                    <i class="fas fa-phone text-gray-400 mr-2"></i>
                                    <span>+62{{ $customer->phone }}</span>
                                </div>
                                @else
                                <span class="text-gray-400">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 max-w-xs truncate">
                                @if($customer->address)
                                <div class="flex items-start">
                                    <i class="fas fa-map-marker-alt text-gray-400 mr-2 mt-0.5"></i>
                                    <span>{{ $customer->address }}</span>
                                </div>
                                @else
                                <span class="text-gray-400">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    {{ $customer->transactions_count > 5 ? 'bg-green-100 text-green-800' :
                                       ($customer->transactions_count > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ $customer->transactions_count }}
                                    <i class="fas fa-shopping-cart ml-1"></i>
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $customer->created_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $customer->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('customers.show', $customer) }}"
                                   class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded-lg transition duration-200"
                                   title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('customers.edit', $customer) }}"
                                   class="text-green-600 hover:text-green-900 p-2 hover:bg-green-50 rounded-lg transition duration-200"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button"
                                        onclick="confirmDelete({{ $customer->id }}, '{{ $customer->name }}')"
                                        class="text-red-600 hover:text-red-900 p-2 hover:bg-red-50 rounded-lg transition duration-200"
                                        title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <a href="{{ route('transactions.create') }}?customer_id={{ $customer->id }}"
                                   class="text-purple-600 hover:text-purple-900 p-2 hover:bg-purple-50 rounded-lg transition duration-200"
                                   title="Buat Transaksi Baru">
                                    <i class="fas fa-plus-circle"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center">
                            <div class="text-center">
                                <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                                <p class="text-gray-500 text-lg">Belum ada pelanggan terdaftar</p>
                                <a href="{{ route('customers.create') }}" class="text-blue-600 hover:text-blue-800 mt-2 inline-block font-medium">
                                    <i class="fas fa-plus mr-1"></i> Tambah Pelanggan Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Summary -->
    @if($customers->count() > 0)
    <div class="mt-6 text-sm text-gray-600">
        Menampilkan <span class="font-medium">{{ $customers->count() }}</span> pelanggan
    </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 text-center mb-2">Hapus Pelanggan</h3>
            <div class="px-7 py-3">
                <p class="text-sm text-gray-500 text-center" id="deleteMessage">
                    Apakah Anda yakin ingin menghapus pelanggan ini?
                </p>
                <p class="text-sm font-medium text-gray-700 text-center mt-2" id="customerName"></p>
            </div>
            <div class="items-center px-4 py-3 flex justify-center space-x-3">
                <form method="POST" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300 transition duration-200">
                        Ya, Hapus
                    </button>
                </form>
                <button onclick="closeModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 transition duration-200">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Smooth hover effects */
    tr {
        transition: background-color 0.15s ease;
    }

    /* Custom scrollbar */
    .overflow-x-auto::-webkit-scrollbar {
        height: 6px;
    }

    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const sortSelect = document.getElementById('sortSelect');
        const resetFilter = document.getElementById('resetFilter');
        const customersTable = document.getElementById('customersTable');
        const rows = Array.from(customersTable.querySelectorAll('tbody tr'));

        // Search functionality
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Sort functionality
        sortSelect.addEventListener('change', function() {
            const value = this.value;

            rows.sort((a, b) => {
                const aName = a.querySelector('td:first-child .text-sm').textContent.toLowerCase();
                const bName = b.querySelector('td:first-child .text-sm').textContent.toLowerCase();
                const aDate = new Date(a.querySelector('td:nth-child(5) .text-sm').textContent.split('/').reverse().join('-'));
                const bDate = new Date(b.querySelector('td:nth-child(5) .text-sm').textContent.split('/').reverse().join('-'));
                const aTransactions = parseInt(a.querySelector('td:nth-child(4) span').textContent);
                const bTransactions = parseInt(b.querySelector('td:nth-child(4) span').textContent);

                switch(value) {
                    case 'name_asc':
                        return aName.localeCompare(bName);
                    case 'name_desc':
                        return bName.localeCompare(aName);
                    case 'recent':
                        return bDate - aDate;
                    case 'oldest':
                        return aDate - bDate;
                    case 'transactions_desc':
                        return bTransactions - aTransactions;
                    default:
                        return 0;
                }
            });

            // Reorder table rows
            const tbody = customersTable.querySelector('tbody');
            rows.forEach(row => tbody.appendChild(row));
        });

        // Reset filter
        resetFilter.addEventListener('click', function() {
            searchInput.value = '';
            sortSelect.value = 'name_asc';

            rows.forEach(row => {
                row.style.display = '';
            });

            // Reset to original order
            const tbody = customersTable.querySelector('tbody');
            const originalRows = Array.from(tbody.querySelectorAll('tr'));
            originalRows.sort((a, b) => {
                return Array.from(tbody.children).indexOf(a) - Array.from(tbody.children).indexOf(b);
            });
            originalRows.forEach(row => tbody.appendChild(row));
        });

        // Highlight row on hover with delay
        let hoverTimeout;
        rows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                clearTimeout(hoverTimeout);
                this.classList.add('bg-blue-50');
            });

            row.addEventListener('mouseleave', function() {
                hoverTimeout = setTimeout(() => {
                    this.classList.remove('bg-blue-50');
                }, 100);
            });
        });
    });

    // Delete confirmation modal
    function confirmDelete(id, name) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        const message = document.getElementById('deleteMessage');
        const customerName = document.getElementById('customerName');

        form.action = `/customers/${id}`;
        customerName.textContent = name;
        message.textContent = `Apakah Anda yakin ingin menghapus pelanggan "${name}"?`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('deleteModal');
        if (event.target == modal) {
            closeModal();
        }
    }

    // Quick actions with keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + F for search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            document.getElementById('searchInput').focus();
        }

        // Escape to close modal
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>
@endpush
@endsection
