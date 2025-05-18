<x-guest-layout>
    <div class="container w-full px-5 py-6 mx-auto">
        <div class="flex items-center min-h-screen bg-gray-50">
            <div class="flex-1 h-full max-w-4xl mx-auto bg-white rounded-lg shadow-xl">
                <div class="flex flex-col md:flex-row">
                    <div class="h-32 md:h-auto md:w-1/2">
                        <img class="object-cover w-full h-full" src="{{ asset('images/Restaurant.jpeg') }}" alt="img" />
                    </div>
                    <div class="flex items-center justify-center p-6 sm:p-12 md:w-1/2">
                        <div class="w-full">
                            <h3 class="mb-4 text-xl font-bold text-blue-600">Order Menu</h3>

                            <form method="POST" action="{{ route('reservations.store.step-three') }}">
                                @csrf

                                <div class="sm:col-span-6 pt-5">
                                    <label for="menus" class="block text-sm font-medium text-gray-700">Select Menu Items</label>
                                    <div class="mt-1">
                                        <select id="menus" name="menu_items[]" class="form-multiselect block w-full mt-1" multiple>
                                            @foreach ($menus as $menu)
                                                <option value="{{ $menu->id }}">
                                                    {{ $menu->name }} (Rp {{ number_format($menu->price, 0, ',', '.') }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('menu_items')
                                        <div class="text-sm text-red-400">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mt-6 p-4 flex justify-between">
                                    <a href="{{ route('reservations.step-two') }}" class="px-4 py-2 bg-indigo-500 hover:bg-indigo-700 rounded-lg text-white">Previous</a>
                                    <button type="submit" class="px-4 py-2 bg-indigo-500 hover:bg-indigo-700 rounded-lg text-white">Next</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
