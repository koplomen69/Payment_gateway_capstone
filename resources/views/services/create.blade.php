@extends('layouts.app')

@section('title', 'Tambah Layanan Baru - Ananda Laundry')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Tambah Layanan Baru</h1>
                <p class="text-gray-600 mt-2">Tambahkan jenis layanan laundry baru ke sistem</p>
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

            <form action="{{ route('services.store') }}" method="POST" id="serviceForm">
                @csrf

                <!-- Service Information Section -->
                <div class="mb-8">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-concierge-bell text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">Informasi Layanan</h3>
                            <p class="text-gray-600">Data dasar layanan laundry</p>
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
                                   value="{{ old('name') }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   placeholder="Contoh: Cuci Reguler, Dry Cleaning, Cuci Express"
                                   required
                                   autofocus>
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Nama layanan yang akan ditampilkan kepada pelanggan</p>
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
                                       value="{{ old('price') }}"
                                       class="w-full border border-gray-300 rounded-lg pl-12 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                       placeholder="10000"
                                       min="0"
                                       step="100"
                                       required>
                            </div>
                            @error('price')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Harga satuan layanan dalam Rupiah</p>
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
                                <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilogram (kg) - Untuk pakaian biasa</option>
                                <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>Piece (pcs) - Untuk item khusus</option>
                            </select>
                            @error('unit')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">
                                <strong>kg:</strong> Untuk cuci reguler, express, dll. |
                                <strong>pcs:</strong> Untuk dry cleaning, helm, tas, dll.
                            </p>
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
                                      placeholder="Deskripsi detail layanan, contoh: Layanan cuci reguler dengan proses 2-3 hari, menggunakan deterjen premium, dll.">{{ old('description') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Deskripsi akan ditampilkan saat pelanggan memilih layanan</p>
                        </div>
                    </div>
                </div>

                <!-- Price Preview -->
                <div class="mb-8 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                    <h4 class="font-semibold text-blue-800 mb-4 flex items-center">
                        <i class="fas fa-eye mr-2"></i>
                        Preview Harga
                    </h4>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Nama Layanan:</p>
                            <p id="previewName" class="text-lg font-bold text-gray-800">-</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Harga:</p>
                            <p id="previewPrice" class="text-2xl font-bold text-green-600">Rp 0</p>
                            <p id="previewUnit" class="text-sm text-gray-600">per -</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button type="submit"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-semibold flex items-center justify-center space-x-2 transition duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                            <i class="fas fa-save"></i>
                            <span>Simpan Layanan</span>
                        </button>

                        <button type="reset"
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-3 px-6 rounded-lg font-semibold flex items-center justify-center space-x-2 transition duration-200">
                            <i class="fas fa-redo"></i>
                            <span>Reset Form</span>
                        </button>

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
        const nameInput = document.getElementById('name');
        const priceInput = document.getElementById('price');
        const unitSelect = document.getElementById('unit');
        const previewName = document.getElementById('previewName');
        const previewPrice = document.getElementById('previewPrice');
        const previewUnit = document.getElementById('previewUnit');

        // Update price preview
        function updatePreview() {
            const name = nameInput.value || '-';
            const price = priceInput.value || 0;
            const unit = unitSelect.value || '-';

            previewName.textContent = name;

            if (price > 0) {
                const formattedPrice = new Intl.NumberFormat('id-ID').format(price);
                previewPrice.textContent = 'Rp ' + formattedPrice;
            } else {
                previewPrice.textContent = 'Rp 0';
            }

            previewUnit.textContent = `per ${unit}`;
        }

        // Event listeners for preview updates
        nameInput.addEventListener('input', updatePreview);
        priceInput.addEventListener('input', updatePreview);
        unitSelect.addEventListener('change', updatePreview);

        // Initial preview update
        updatePreview();

        // Form validation before submit
        form.addEventListener('submit', function(e) {
            const name = nameInput.value.trim();
            const price = priceInput.value;
            const unit = unitSelect.value;

            // Validate name
            if (!name) {
                e.preventDefault();
                showError('Nama layanan harus diisi!', nameInput);
                return;
            }

            // Validate price
            if (!price || price <= 0) {
                e.preventDefault();
                showError('Harga harus lebih dari 0!', priceInput);
                return;
            }

            // Validate unit
            if (!unit) {
                e.preventDefault();
                showError('Satuan harus dipilih!', unitSelect);
                return;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            submitBtn.disabled = true;
        });

        function showError(message, element) {
            // Create error alert
            const alertDiv = document.createElement('div');
            alertDiv.className = 'mb-6 p-4 bg-red-50 border border-red-200 rounded-lg';
            alertDiv.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-600 mr-3"></i>
                    <div>
                        <p class="font-medium text-red-800">${message}</p>
                    </div>
                </div>
            `;

            // Insert before form
            const formCard = document.querySelector('.bg-white.rounded-xl');
            formCard.insertBefore(alertDiv, formCard.firstChild);

            // Focus on the problematic element
            element.focus();
            element.classList.add('border-red-500', 'ring-2', 'ring-red-200');

            // Remove error styling after 3 seconds
            setTimeout(() => {
                element.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 3000);

            // Scroll to error
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Format price input
        priceInput.addEventListener('blur', function() {
            if (this.value) {
                const value = parseInt(this.value.replace(/\D/g, ''));
                if (!isNaN(value)) {
                    this.value = value;
                }
            }
        });

        // Auto-focus name field
        if (!nameInput.value) {
            nameInput.focus();
        }
    });
</script>
@endpush
@endsection
