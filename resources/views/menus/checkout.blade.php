<x-guest-layout>
    <div class="container w-full px-5 py-6 mx-auto">
        <h2 class="text-2xl font-semibold mb-6">Checkout</h2>

        @if(session()->has('cart'))
            <div class="mb-6">
                <h3>Your Cart</h3>
                <ul>
                    @php
                        $totalPrice = 0;
                        foreach (session()->get('cart') as $menuId => $item) {
                            $totalPrice += $item['price'] * $item['quantity'];
                        }

                        // Menentukan persentase pajak (misalnya 10%)
                        $taxRate = 0.10; // 10% Pajak
                        $taxAmount = $totalPrice * $taxRate;
                        $totalWithTax = $totalPrice + $taxAmount;
                    @endphp

                    @foreach (session()->get('cart') as $menuId => $item)
                        <li class="flex justify-between mb-4">
                            <span>{{ $item['name'] }} (x{{ $item['quantity'] }})</span>
                            <span>{{ 'Rp. ' . number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Menampilkan Total Harga dan Pajak -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold">Total:</h3>
                <p class="flex justify-between mb-2">
                    <span>Subtotal</span>
                    <span>{{ 'Rp. ' . number_format($totalPrice, 0, ',', '.') }}</span>
                </p>
                <p class="flex justify-between mb-2">
                    <span>Tax (10%)</span>
                    <span>{{ 'Rp. ' . number_format($taxAmount, 0, ',', '.') }}</span>
                </p>
                <p class="flex justify-between mb-4">
                    <span>Total with Tax</span>
                    <span>{{ 'Rp. ' . number_format($totalWithTax, 0, ',', '.') }}</span>
                </p>
            </div>
        @endif

        <form action="{{ route('menus.placeOrder') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="name" class="block">Your Name</label>
                <input type="text" id="name" name="name" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label for="email" class="block">Your Email</label>
                <input type="text" id="email" name="email" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label for="phone" class="block">Your Phone Number</label>
                <input type="text" id="phone" name="phone" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label for="table_id" class="block">Table Number</label>
                <select id="table_id" name="table_id" class="w-full border rounded px-3 py-2" required>
                    @foreach ($tables as $table)
                        <option value="{{ $table->id }}">
                            {{ $table->name }} - {{ $table->location }} ({{ $table->guest_number }} guests)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="note" class="block">Note (optional)</label>
                <textarea id="note" name="note" class="w-full border rounded px-3 py-2" rows="4" placeholder="Add any special requests or notes"></textarea>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('menus.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Back to Menu</a>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Place Order</button>
            </div>
        </form>
    </div>
</x-guest-layout>
