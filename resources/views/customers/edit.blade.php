@extends('layouts.app')

@section('title', 'Edit Pelanggan - Ananda Laundry')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Edit Data Pelanggan</h1>
                <p class="text-gray-600 mt-2">Perbarui informasi pelanggan: {{ $customer->name }}</p>
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

            <form action="{{ route('customers.update', $customer) }}" method="POST" id="customerForm">
                @csrf
                @method('PUT')

                <!-- Customer Information Section -->
                <div class="mb-8">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user-edit text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">Informasi Pelanggan</h3>
                            <p class="text-gray-600">Perbarui data pelanggan</p>
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
                                   value="{{ old('name', $customer->name) }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   placeholder="Masukkan nama lengkap pelanggan"
                                   required
                                   autofocus>
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
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
                                       value="{{ old('phone', $customer->phone) }}"
                                       class="w-full border border-gray-300 rounded-lg pl-12 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                       placeholder="81234567890"
                                       pattern="[0-9]*">
                            </div>
                            @error('phone')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
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
                                      placeholder="Masukkan alamat lengkap pelanggan">{{ old('address', $customer->address) }}</textarea>
                            @error('address')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
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
                                  placeholder="Contoh: Pelanggan VIP, Alergi deterjen tertentu, dll.">{{ old('notes', $customer->notes) }}</textarea>
                    </div>
                </div>

                <!-- Customer Stats -->
                <div class="mb-8 bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
                        Statistik Pelanggan
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Total Transaksi</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $customer->transactions_count ?? 0 }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Total Belanja</p>
                            <p class="text-xl font-bold text-green-600">Rp {{ number_format($customer->total_spent ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Terdaftar Sejak</p>
                            <p class="text-lg font-medium text-gray-800">{{ $customer->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button type="submit"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-semibold flex items-center justify-center space-x-2 transition duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                            <i class="fas fa-save"></i>
                            <span>Update Data</span>
                        </button>

                        <a href="{{ route('customers.show', $customer) }}"
                           class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 py-3 px-6 rounded-lg font-semibold flex items-center justify-center space-x-2 transition duration-200">
                            <i class="fas fa-eye"></i>
                            <span>Lihat Detail</span>
                        </a>

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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('customerForm');
        const phoneInput = document.getElementById('phone');

        // Format phone number input
        phoneInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');

            if (value.length > 0) {
                if (value.startsWith('0')) {
                    value = value.substring(1);
                }
                value = value.substring(0, 15);
            }

            this.value = value;
        });

        // Form validation
        form.addEventListener('submit', function(e) {
            const nameInput = document.getElementById('name');

            if (!nameInput.value.trim()) {
                e.preventDefault();
                alert('Nama pelanggan harus diisi!');
                nameInput.focus();
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
