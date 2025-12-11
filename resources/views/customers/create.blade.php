@extends('layouts.app')

@section('title', 'Tambah Pelanggan Baru - Ananda Laundry')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Tambah Pelanggan Baru</h1>
                <p class="text-gray-600 mt-2">Isi data pelanggan untuk ditambahkan ke sistem</p>
            </div>
            <div>
                <a href="{{ route('customers.index') }}"
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

            <form action="{{ route('customers.store') }}" method="POST" id="customerForm">
                @csrf

                <!-- Customer Information Section -->
                <div class="mb-8">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">Informasi Pelanggan</h3>
                            <p class="text-gray-600">Data dasar pelanggan</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <!-- Name Field -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   value="{{ old('name') }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   placeholder="Masukkan nama lengkap pelanggan"
                                   required
                                   autofocus>
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Contoh: Budi Santoso, Sari Dewi, dll.</p>
                        </div>

                        <!-- Phone Field -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Nomor Telepon
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">+62</span>
                                </div>
                                <input type="tel"
                                       name="phone"
                                       id="phone"
                                       value="{{ old('phone') }}"
                                       class="w-full border border-gray-300 rounded-lg pl-12 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                       placeholder="81234567890"
                                       pattern="[0-9]*">
                            </div>
                            @error('phone')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Contoh: 081234567890 (tanpa +62)</p>
                        </div>

                        <!-- Address Field -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                Alamat Lengkap
                            </label>
                            <textarea name="address"
                                      id="address"
                                      rows="4"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                      placeholder="Masukkan alamat lengkap pelanggan">{{ old('address') }}</textarea>
                            @error('address')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Jalan, Nomor Rumah, RT/RW, Kelurahan, Kecamatan, Kota</p>
                        </div>
                    </div>
                </div>

                <!-- Additional Information Section -->
                <div class="mb-8">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-info-circle text-purple-600"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">Informasi Tambahan</h3>
                            <p class="text-gray-600">Catatan khusus (opsional)</p>
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Catatan Pelanggan
                        </label>
                        <textarea name="notes"
                                  id="notes"
                                  rows="3"
                                  class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                  placeholder="Contoh: Pelanggan VIP, Alergi deterjen tertentu, dll.">{{ old('notes') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Catatan ini akan membantu dalam memberikan pelayanan terbaik</p>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button type="submit"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-semibold flex items-center justify-center space-x-2 transition duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                            <i class="fas fa-save"></i>
                            <span>Simpan Pelanggan</span>
                        </button>

                        <button type="reset"
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-3 px-6 rounded-lg font-semibold flex items-center justify-center space-x-2 transition duration-200">
                            <i class="fas fa-redo"></i>
                            <span>Reset Form</span>
                        </button>

                        <a href="{{ route('customers.index') }}"
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

@push('styles')
<style>
    /* Custom form styling */
    input:focus, textarea:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Smooth transitions */
    .transition {
        transition: all 0.2s ease-in-out;
    }

    /* Phone input styling */
    input[type="tel"]::-webkit-inner-spin-button,
    input[type="tel"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type="tel"] {
        -moz-appearance: textfield;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('customerForm');
        const phoneInput = document.getElementById('phone');

        // Format phone number input
        phoneInput.addEventListener('input', function() {
            // Remove non-numeric characters
            let value = this.value.replace(/\D/g, '');

            // Format to Indonesian phone number
            if (value.length > 0) {
                // Remove leading 0 if present
                if (value.startsWith('0')) {
                    value = value.substring(1);
                }

                // Limit to 15 digits
                value = value.substring(0, 15);
            }

            this.value = value;
        });

        // Form validation before submit
        form.addEventListener('submit', function(e) {
            const nameInput = document.getElementById('name');
            const phoneInput = document.getElementById('phone');

            // Validate name
            if (!nameInput.value.trim()) {
                e.preventDefault();
                showError('Nama pelanggan harus diisi!', nameInput);
                return;
            }

            // Validate phone number format
            if (phoneInput.value) {
                const phoneRegex = /^[0-9]{10,15}$/;
                if (!phoneRegex.test(phoneInput.value)) {
                    e.preventDefault();
                    showError('Nomor telepon harus 10-15 digit angka!', phoneInput);
                    return;
                }
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
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
            form.insertBefore(alertDiv, form.firstChild);

            // Focus on the problematic element
            element.focus();
            element.classList.add('border-red-500', 'ring-2', 'ring-red-200');

            // Remove error styling after 3 seconds
            setTimeout(() => {
                element.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
            }, 3000);

            // Scroll to error
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Real-time name validation
        const nameInput = document.getElementById('name');
        nameInput.addEventListener('blur', function() {
            if (this.value.trim()) {
                this.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
                this.classList.add('border-green-500');

                // Remove green border after 1 second
                setTimeout(() => {
                    this.classList.remove('border-green-500');
                }, 1000);
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
