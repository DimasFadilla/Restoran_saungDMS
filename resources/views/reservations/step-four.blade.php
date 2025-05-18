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
                            <h3 class="mb-4 text-xl font-bold text-blue-600">Payment</h3>
                            
                            <form method="POST" action="{{ route('reservations.store.step-four') }}">
                                @csrf
                                <div class="sm:col-span-6 pt-5">
                                    <label for="total_cost" class="block text-sm font-medium text-gray-700">Total Cost</label>
                                    <div class="mt-1">
                                        <input type="text" id="total_cost" name="total_cost" value="{{ $totalCost }}" readonly
                                            class="block w-full appearance-none bg-white border border-gray-400 rounded-md py-2 px-3 text-base leading-normal transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
                                    </div>
                                </div>

                                <div class="mt-6 p-4 flex justify-between">
                                    <a href="{{ route('reservations.step-three') }}" class="px-4 py-2 bg-indigo-500 hover:bg-indigo-700 rounded-lg text-white">Previous</a>
                                    <button type="submit" class="px-4 py-2 bg-indigo-500 hover:bg-indigo-700 rounded-lg text-white">complete</button>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
