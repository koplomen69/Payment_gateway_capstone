@extends('layouts.app')

@section('title', 'Edit Layanan - Ananda Laundry')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Edit Layanan</h1>
                <p class="text-gray-600 mt-2">Perbarui informasi layanan: {{ $service->name }}</p>
            </div>
            <div>
                <a href="{{ route('services.index') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2.5 rounded-lg flex items-center space-x-2 transition duration-200 shadow-md">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali ke Daftar</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
            <!-- Success/Error Messages -->
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

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-3"></i>
                        <div>
                            <p class="font-medium text-red-800">Terdapat kesalahan dalam pengisian form:</p>
                            <ul class="mt-2 list-disc list-inside text-sm text-red-600">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('services.update', $service) }}" method="POST" id="serviceForm">
                @csrf
                @method('PUT')

                <!-- Service Information Section -->
                <div class="mb-8">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-concierge-bell text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">Informasi Layanan</h3>
                            <p class="text-gray-600">Perbarui data layanan laundry</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <!-- Name Field -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Layanan <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   value="{{ old('name', $service->name) }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   placeholder="Contoh: Cuci Reguler, Dry Cleaning, Cuci Express"
                                   required
                                   autofocus>
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Price Field -->
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                                Harga <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">Rp</span>
                                </div>
                                <input type="number"
                                       name="price"
                                       id="price"
                                       value="{{ old('price', $service->price) }}"
                                       class="w-full border border-gray-300 rounded-lg pl-12 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                       placeholder="10000"
                                       min="0"
                                       step="100"
                                       required>
                            </div>
                            @error('price')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Unit Field -->
                        <div>
                            <label for="unit" class="block text-sm font-medium text-gray-700 mb-2">
                                Satuan <span class="text-red-500">*</span>
                            </label>
                            <select name="unit"
                                    id="unit"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                    required>
                                <option value="">-- Pilih Satuan --</option>
                                <option value="kg" {{ old('unit', $service->unit) == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                <option value="pcs" {{ old('unit', $service->unit) == 'pcs' ? 'selected' : '' }}>Piece (pcs)</option>
                            </select>
                            @error('unit')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description Field -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Deskripsi Layanan (Opsional)
                            </label>
                            <textarea name="description"
                                      id="description"
                                      rows="4"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                      placeholder="Deskripsi detail layanan">{{ old('description', $service->description) }}</textarea>
                        </div>

                        <!-- Is Active Field (opsional, karena hanya sometimes di controller) -->
                        @if(isset($service->is_active))
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Status Layanan</label>
                            <div class="space-y-3">
                                <label class="flex items-center space-x-3 p-3 border-2 {{ old('is_active', $service->is_active) == 1 ? 'border-green-200 bg-green-50' : 'border-gray-300' }} rounded-lg cursor-pointer hover:bg-green-50 transition duration-200">
                                    <input type="radio"
                                           name="is_active"
                                           value="1"
                                           class="text-green-600 focus:ring-green-500"
                                           {{ old('is_active', $service->is_active) == 1 ? 'checked' : '' }}>
                                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                                    <span class="flex-1 font-medium">Aktif</span>
                                    <span class="bg-green-100 text-green-800 text-xs px-3 py-1 rounded-full font-bold">TAMPIL</span>
                                </label>

                                <label class="flex items-center space-x-3 p-3 border-2 {{ old('is_active', $service->is_active) == 0 ? 'border-gray-200 bg-gray-50' : 'border-gray-300' }} rounded-lg cursor-pointer hover:bg-gray-50 transition duration-200">
                                    <input type="radio"
                                           name="is_active"
                                           value="0"
                                           class="text-gray-600 focus:ring-gray-500"
                                           {{ old('is_active', $service->is_active) == 0 ? 'checked' : '' }}>
                                    <i class="fas fa-pause-circle text-gray-600 text-xl"></i>
                                    <span class="flex-1 font-medium">Nonaktif</span>
                                    <span class="bg-gray-100 text-gray-800 text-xs px-3 py-1 rounded-full font-bold">SEMENTARA</span>
                                </label>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Service Stats -->
                <div class="mb-8 bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
                        Informasi Layanan
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Harga Saat Ini</p>
                            <p class="text-xl font-bold text-green-600">
                                Rp {{ number_format($service->price, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Satuan</p>
                            <p class="text-lg font-medium text-blue-800">
                                {{ strtoupper($service->unit) }}
                            </p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Terdaftar Sejak</p>
                            <p class="text-lg font-medium text-gray-800">
                                {{ $service->created_at->format('d M Y') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button type="submit"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-semibold flex items-center justify-center space-x-2 transition duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                            <i class="fas fa-save"></i>
                            <span>Update Layanan</span>
                        </button>

                        <a href="{{ route('services.show', $service) }}"
                           class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-3 px-6 rounded-lg font-semibold flex items-center justify-center space-x-2 transition duration-200">
                            <i class="fas fa-eye"></i>
                            <span>Lihat Detail</span>
                        </a>

                        <a href="{{ route('services.index') }}"
                           class="flex-1 bg-white border border-gray-300 hover:bg-gray-50 text-gray-800 py-3 px-6 rounded-lg font-semibold flex items-center justify-center space-x-2 transition duration-200">
                            <i class="fas fa-times"></i>
                            <span>Batal</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('serviceForm');

        // Form validation
        form.addEventListener('submit', function(e) {
            const nameInput = document.getElementById('name');
            const priceInput = document.getElementById('price');
            const unitSelect = document.getElementById('unit');

            // Validate name
            if (!nameInput.value.trim()) {
                e.preventDefault();
                alert('Nama layanan harus diisi!');
                nameInput.focus();
                return;
            }

            // Validate price
            if (!priceInput.value || priceInput.value <= 0) {
                e.preventDefault();
                alert('Harga harus lebih dari 0!');
                priceInput.focus();
                return;
            }

            // Validate unit
            if (!unitSelect.value) {
                e.preventDefault();
                alert('Satuan harus dipilih!');
                unitSelect.focus();
                return;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memperbarui...';
            submitBtn.disabled = true;
        });
    });
</script>
@endpush
@endsection
