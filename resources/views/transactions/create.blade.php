<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Transaksi - Ananda Laundry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Tambah Transaksi Baru</h1>
                <p class="text-gray-600">Sistem Informasi Keuangan Ananda Laundry</p>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <form action="{{ route('transactions.store') }}" method="POST" id="transactionForm">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Customer Selection -->
                        <div class="col-span-2">
                            <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Pilih Pelanggan <span class="text-red-500">*</span>
                            </label>
                            <select name="customer_id" id="customer_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">-- Pilih Pelanggan --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" data-phone="{{ $customer->phone }}">
                                        {{ $customer->name }} - {{ $customer->phone }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Service Selection -->
                        <div class="col-span-2">
                            <label for="service_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Pilih Layanan <span class="text-red-500">*</span>
                            </label>
                            <select name="service_id" id="service_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">-- Pilih Layanan --</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" data-price="{{ $service->price }}" data-unit="{{ $service->unit }}">
                                        {{ $service->name }} - Rp {{ number_format($service->price, 0, ',', '.') }}/{{ $service->unit }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Quantity & Price -->
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                Jumlah (kg) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="quantity" id="quantity"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   step="0.1" min="0.1" required>
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                                Harga Satuan
                            </label>
                            <input type="text" id="price"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50"
                                   readonly>
                        </div>

                        <!-- Total Amount -->
                        <div class="col-span-2">
                            <label for="total_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                Total Amount
                            </label>
                            <input type="text" id="total_amount"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-lg font-bold text-green-600"
                                   readonly>
                        </div>

                        <!-- Payment Method -->
                        <div class="col-span-2">
                            <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                                Metode Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <select name="payment_method" id="payment_method"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="cash">Tunai</option>
                                <option value="midtrans">Midtrans (Digital Payment)</option>
                                <option value="transfer">Transfer Bank</option>
                            </select>
                            @error('payment_method')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Catatan tambahan..."></textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-8 flex justify-end space-x-4">
                        <a href="{{ route('transactions.index') }}"
                           class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                        <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                            <i class="fas fa-save mr-2"></i>Simpan Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Auto calculation
        document.addEventListener('DOMContentLoaded', function() {
            const serviceSelect = document.getElementById('service_id');
            const quantityInput = document.getElementById('quantity');
            const priceInput = document.getElementById('price');
            const totalInput = document.getElementById('total_amount');

            function calculateTotal() {
                const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
                const price = selectedOption.dataset.price;
                const quantity = quantityInput.value;
                const unit = selectedOption.dataset.unit;

                if (price && quantity) {
                    const total = price * quantity;
                    priceInput.value = 'Rp ' + Number(price).toLocaleString('id-ID') + '/' + unit;
                    totalInput.value = 'Rp ' + Number(total).toLocaleString('id-ID');
                } else {
                    priceInput.value = '';
                    totalInput.value = '';
                }
            }

            serviceSelect.addEventListener('change', calculateTotal);
            quantityInput.addEventListener('input', calculateTotal);

            // Initial calculation if values are pre-filled
            calculateTotal();
        });
    </script>
</body>
</html>
