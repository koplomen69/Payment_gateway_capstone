@extends('layouts.app')

@section('title', 'Daftar Layanan - Ananda Laundry')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Daftar Layanan</h1>
                <p class="text-gray-600 mt-2">Kelola jenis layanan laundry yang tersedia</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('services.create') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg flex items-center space-x-2 transition duration-200 shadow-md">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Layanan</span>
                </a>
                <a href="{{ route('dashboard') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2.5 rounded-lg flex items-center space-x-2 transition duration-200 shadow-md">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
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

    <!-- Services Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($services as $service)
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow duration-300">
            <!-- Service Header -->
            <div class="p-6 border-b border-gray-100">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">{{ $service->name }}</h3>
                        <div class="flex items-center mt-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                {{ $service->unit == 'kg' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                <i class="fas fa-{{ $service->unit == 'kg' ? 'weight' : 'cube' }} mr-1"></i>
                                {{ strtoupper($service->unit) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('services.edit', $service) }}"
                           class="text-green-600 hover:text-green-900 p-2 hover:bg-green-50 rounded-lg transition duration-200"
                           title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button"
                                onclick="confirmDelete({{ $service->id }}, '{{ $service->name }}')"
                                class="text-red-600 hover:text-red-900 p-2 hover:bg-red-50 rounded-lg transition duration-200"
                                title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Service Details -->
            <div class="p-6">
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">Harga Satuan</span>
                        <span class="text-2xl font-bold text-green-600">
                            Rp {{ number_format($service->price, 0, ',', '.') }}/{{ $service->unit }}
                        </span>
                    </div>

                    @if($service->description)
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-700">{{ $service->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Service Footer -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">
                        <i class="far fa-clock mr-1"></i>
                        {{ $service->created_at->diffForHumans() }}
                    </span>
                    <a href="{{ route('transactions.create') }}?service_id={{ $service->id }}"
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Buat Transaksi <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="md:col-span-2 lg:col-span-3">
            <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                <i class="fas fa-concierge-bell text-gray-300 text-6xl mb-6"></i>
                <h3 class="text-2xl font-bold text-gray-700 mb-3">Belum Ada Layanan</h3>
                <p class="text-gray-600 mb-6 max-w-md mx-auto">
                    Tambahkan layanan laundry pertama Anda untuk mulai menerima transaksi
                </p>
                <a href="{{ route('services.create') }}"
                   class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-200 shadow-md">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Layanan Pertama
                </a>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Summary -->
    @if($services->count() > 0)
    <div class="mt-8 p-6 bg-white rounded-xl shadow-lg">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Ringkasan Layanan</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <p class="text-2xl font-bold text-blue-600">{{ $services->count() }}</p>
                <p class="text-sm text-gray-600">Total Layanan</p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-2xl font-bold text-green-600">
                    @php
                        $avgPrice = $services->avg('price');
                    @endphp
                    Rp {{ number_format($avgPrice, 0, ',', '.') }}
                </p>
                <p class="text-sm text-gray-600">Rata-rata Harga</p>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <p class="text-2xl font-bold text-purple-600">
                    {{ $services->where('unit', 'kg')->count() }}
                </p>
                <p class="text-sm text-gray-600">Layanan per KG</p>
            </div>
        </div>
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
            <h3 class="text-lg font-medium text-gray-900 text-center mb-2">Hapus Layanan</h3>
            <div class="px-7 py-3">
                <p class="text-sm text-gray-500 text-center" id="deleteMessage">
                    Apakah Anda yakin ingin menghapus layanan ini?
                </p>
                <p class="text-sm font-medium text-gray-700 text-center mt-2" id="serviceName"></p>
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

@push('scripts')
<script>
    function confirmDelete(id, name) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        const message = document.getElementById('deleteMessage');
        const serviceName = document.getElementById('serviceName');

        form.action = `/services/${id}`;
        serviceName.textContent = name;
        message.textContent = `Apakah Anda yakin ingin menghapus layanan "${name}"?`;

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
</script>
@endpush
@endsection
