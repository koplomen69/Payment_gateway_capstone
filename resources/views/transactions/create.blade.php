@extends('layouts.app')

@section('title', 'Tambah Transaksi Baru')

@section('subtitle', 'Buat transaksi laundry baru')

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('transactions.store') }}" method="POST" id="transactionForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column - Customer & Service -->
            <div class="space-y-6">
                <!-- Customer Information Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Data Pelanggan</h3>
                            <p class="text-sm text-gray-600">Isi data pelanggan</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <!-- Customer Name Input -->
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Pelanggan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="customer_name" id="customer_name"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Masukkan nama pelanggan" required value="{{ old('customer_name') }}">
                            @error('customer_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Customer Phone Input -->
                        <div>
                            <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Nomor Telepon
                            </label>
                            <input type="text" name="customer_phone" id="customer_phone"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Contoh: 081234567890" value="{{ old('customer_phone') }}">
                            @error('customer_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Customer Address Input -->
                        <div>
                            <label for="customer_address" class="block text-sm font-medium text-gray-700 mb-2">
                                Alamat
                            </label>
                            <textarea name="customer_address" id="customer_address" rows="3"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Masukkan alamat pelanggan">{{ old('customer_address') }}</textarea>
                            @error('customer_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Service Information Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-concierge-bell text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Pilih Layanan Laundry</h3>
                            <p class="text-sm text-gray-600">Pilih jenis layanan laundry yang diinginkan</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <!-- Service Selection -->
                        <div>
                            <label for="service_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Jenis Layanan <span class="text-red-500">*</span>
                            </label>
                            <select name="service_id" id="service_id"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base" required>
                                <option value="">-- Pilih Layanan Laundry --</option>

                                @forelse($services as $service)
                                <option value="{{ $service->id }}"
                                        data-price="{{ $service->price }}"
                                        data-unit="{{ $service->unit }}"
                                        data-description="{{ $service->description ?? '' }}"
                                        {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }} - Rp {{ number_format($service->price, 0, ',', '.') }}/{{ $service->unit }}
                                </option>
                                @empty
                                <option value="" disabled>Tidak ada layanan tersedia</option>
                                @endforelse
                            </select>
                                       

                            </select>
                            @error('service_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Service Description -->
                        <div id="service-description" class="hidden">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                                    <div>
                                        <p class="text-sm font-medium text-blue-800 mb-1">Deskripsi Layanan:</p>
                                        <p class="text-sm text-blue-700" id="service-desc-text"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quantity Input -->
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                Jumlah <span class="text-red-500">*</span>
                                <span id="quantity-unit" class="text-gray-500">(kg)</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="quantity" id="quantity"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-16 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       step="0.1" min="0.1" value="{{ old('quantity', 1) }}" required
                                       placeholder="Masukkan jumlah">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-gray-500 text-sm font-medium" id="quantity-display-unit">kg</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500" id="quantity-help">
                                Minimal 0.1 kg untuk layanan per kg
                            </p>
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Price Display -->
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                                Harga Satuan
                            </label>
                            <input type="text" id="price"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-gray-700 font-medium text-lg"
                                   readonly placeholder="Pilih layanan terlebih dahulu">
                        </div>

                        <!-- Total Preview -->
                        <div id="total-preview" class="hidden">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-green-800">Estimasi Total:</span>
                                    <span id="preview-total" class="text-lg font-bold text-green-600">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Payment & Summary -->
            <div class="space-y-6">
                <!-- Payment Information Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-credit-card text-yellow-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Pembayaran</h3>
                            <p class="text-sm text-gray-600">Pilih metode pembayaran</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Metode Pembayaran <span class="text-red-500">*</span></label>
                            <div class="space-y-3">
                                <label class="flex items-center space-x-3 p-3 border-2 border-green-200 rounded-lg cursor-pointer hover:bg-green-50 transition duration-200 bg-green-50">
                                    <input type="radio" name="payment_method" value="cash" class="text-blue-600 focus:ring-blue-500"
                                           {{ old('payment_method', 'cash') == 'cash' ? 'checked' : '' }}>
                                    <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                                    <span class="flex-1 font-medium">Tunai</span>
                                    <span class="bg-green-100 text-green-800 text-xs px-3 py-1 rounded-full font-bold">INSTANT</span>
                                </label>

                                <label class="flex items-center space-x-3 p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-blue-50 transition duration-200">
                                    <input type="radio" name="payment_method" value="midtrans" class="text-blue-600 focus:ring-blue-500"
                                           {{ old('payment_method') == 'midtrans' ? 'checked' : '' }}>
                                    <i class="fas fa-qrcode text-blue-600 text-xl"></i>
                                    <span class="flex-1 font-medium">Midtrans (QRIS/Transfer)</span>
                                    <span class="bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full font-bold">DIGITAL</span>
                                </label>
                            </div>
                            @error('payment_method')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Status Info -->
                        <div id="payment-status-info" class="bg-gray-50 rounded-lg p-4 border">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-info-circle text-blue-500"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Status Pembayaran:</p>
                                    <p class="text-sm text-gray-600">
                                        Akan otomatis
                                        <span id="status-text" class="font-bold text-green-600">LUNAS</span>
                                        untuk tunai, atau
                                        <span class="font-bold text-yellow-600">PENDING</span>
                                        untuk pembayaran digital
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-sticky-note text-purple-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Catatan Khusus</h3>
                            <p class="text-sm text-gray-600">Instruksi tambahan untuk laundry</p>
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Catatan (Opsional)
                        </label>
                        <textarea name="notes" id="notes" rows="4"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Contoh:
• Cuci saja tanpa setrika
• Ada noda teh di kerah baju
• Bahan mudah luntur, pisahkan
• Lipat rapi, jangan digantung
• Parfum lavender
...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Order Summary Card -->
                <div class="bg-white rounded-lg shadow-lg p-6 border-2 border-blue-300 sticky top-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-receipt text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Ringkasan Pesanan</h3>
                            <p class="text-sm text-gray-600">Detail transaksi laundry</p>
                        </div>
                    </div>

                    <div class="space-y-3 bg-gray-50 rounded-lg p-4 border">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Pelanggan:</span>
                            <span id="summary-customer" class="font-medium text-gray-800 text-right">-</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Layanan:</span>
                            <span id="summary-service" class="font-medium text-gray-800 text-right">-</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Jumlah:</span>
                            <span id="summary-quantity" class="font-medium text-gray-800">-</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Harga Satuan:</span>
                            <span id="summary-price" class="font-medium text-gray-800">-</span>
                        </div>
                        <div class="border-t border-gray-300 pt-3 mt-2">
                            <div class="flex justify-between text-lg font-bold">
                                <span class="text-gray-800">Total Bayar:</span>
                                <span id="summary-total" class="text-green-600">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 space-y-3">
                        <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-semibold flex items-center justify-center space-x-2 transition duration-200 shadow-lg transform hover:scale-105">
                            <i class="fas fa-paper-plane"></i>
                            <span>Proses Transaksi</span>
                        </button>

                        <a href="{{ route('transactions.index') }}"
                           class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 py-3 px-4 rounded-lg font-semibold flex items-center justify-center space-x-2 transition duration-200">
                            <i class="fas fa-arrow-left"></i>
                            <span>Kembali ke Daftar</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const customerNameInput = document.getElementById('customer_name');
    const serviceSelect = document.getElementById('service_id');
    const quantityInput = document.getElementById('quantity');
    const priceInput = document.getElementById('price');
    const totalDisplay = document.getElementById('summary-total');
    const customerDisplay = document.getElementById('summary-customer');
    const serviceDisplay = document.getElementById('summary-service');
    const quantityDisplay = document.getElementById('summary-quantity');
    const priceDisplay = document.getElementById('summary-price');
    const quantityUnit = document.getElementById('quantity-unit');
    const quantityDisplayUnit = document.getElementById('quantity-display-unit');
    const serviceDescription = document.getElementById('service-description');
    const serviceDescText = document.getElementById('service-desc-text');
    const totalPreview = document.getElementById('total-preview');
    const previewTotal = document.getElementById('preview-total');
    const quantityHelp = document.getElementById('quantity-help');

    // Calculate total function
    function calculateTotal() {
        const selectedService = serviceSelect.options[serviceSelect.selectedIndex];
        const price = selectedService.dataset.price;
        const quantity = quantityInput.value;
        const unit = selectedService.dataset.unit;
        const customerName = customerNameInput.value;

        // Update customer name in summary
        customerDisplay.textContent = customerName || '-';

        if (price && quantity) {
            const total = price * quantity;

            // Update displays
            priceInput.value = 'Rp ' + Number(price).toLocaleString('id-ID') + '/' + unit;
            totalDisplay.textContent = 'Rp ' + Number(total).toLocaleString('id-ID');
            previewTotal.textContent = 'Rp ' + Number(total).toLocaleString('id-ID');

            // Get service name without price
            const serviceText = selectedService.text.split(' - ')[0];
            serviceDisplay.textContent = serviceText;
            quantityDisplay.textContent = quantity + ' ' + unit;
            priceDisplay.textContent = 'Rp ' + Number(price).toLocaleString('id-ID');

            // Update unit labels
            quantityUnit.textContent = '(' + unit + ')';
            quantityDisplayUnit.textContent = unit;

            // Show total preview
            totalPreview.classList.remove('hidden');

            // Update quantity help text based on unit
            if (unit === 'kg') {
                quantityHelp.textContent = 'Minimal 0.1 kg untuk layanan per kg';
                quantityInput.step = '0.1';
                quantityInput.min = '0.1';
            } else {
                quantityHelp.textContent = 'Masukkan jumlah item';
                quantityInput.step = '1';
                quantityInput.min = '1';
            }
        } else {
            priceInput.value = '';
            totalDisplay.textContent = 'Rp 0';
            previewTotal.textContent = 'Rp 0';
            serviceDisplay.textContent = '-';
            quantityDisplay.textContent = '-';
            priceDisplay.textContent = '-';
            totalPreview.classList.add('hidden');
        }
    }

    // Customer name input handler
    customerNameInput.addEventListener('input', function() {
        customerDisplay.textContent = this.value || '-';
    });

    // Service selection handler
    serviceSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const description = selectedOption.dataset.description;

        if (description) {
            serviceDescText.textContent = description;
            serviceDescription.classList.remove('hidden');
        } else {
            serviceDescription.classList.add('hidden');
        }

        calculateTotal();
    });

    // Quantity input handler
    quantityInput.addEventListener('input', calculateTotal);

    // Auto-calculate when page loads
    calculateTotal();

    // Form validation before submit
    document.getElementById('transactionForm').addEventListener('submit', function(e) {
        const customerName = customerNameInput.value.trim();
        const serviceId = serviceSelect.value;
        const quantity = quantityInput.value;

        if (!customerName) {
            e.preventDefault();
            alert('❌ Nama pelanggan harus diisi!');
            customerNameInput.focus();
            return;
        }

        if (!serviceId) {
            e.preventDefault();
            alert('❌ Pilih layanan laundry terlebih dahulu!');
            serviceSelect.focus();
            return;
        }

        if (!quantity || quantity <= 0) {
            e.preventDefault();
            alert('❌ Jumlah harus lebih dari 0!');
            quantityInput.focus();
            return;
        }

        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Memproses...</span>';
        submitBtn.disabled = true;
    });
});
</script>
@endsection
