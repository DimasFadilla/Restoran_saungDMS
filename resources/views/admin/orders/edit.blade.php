<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Order') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex m-2 p-2">
                <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 bg-indigo-500 hover:bg-indigo-700 rounded-lg text-white">Back to Orders</a>
            </div>
            <div class="m-2 p-2 bg-slate-100 rounded">
                <div class="space-y-8 divide-y divide-gray-200 w-1/2 mt-10">
                    <form method="POST" action="{{ route('admin.orders.update', $order->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="sm:col-span-6">
                            <label for="payment_status" class="block text-sm font-medium text-gray-700">Payment Status</label>
                            <div class="mt-1">
                                <select name="payment_status" id="payment_status"
                                        class="block w-full appearance-none bg-white border border-gray-400 rounded-md py-2 px-3 text-base leading-normal transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                    <option value="pending" @selected($order->payment_status == 'pending')>Pending</option>
                                    <option value="completed" @selected($order->payment_status == 'completed')>Completed</option>
                                    <option value="failed" @selected($order->payment_status == 'failed')>Failed</option>
                                </select>
                            </div>
                            @error('payment_status')
                                <div class="text-sm text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-6 p-4">
                            <button type="submit" class="px-4 py-2 bg-indigo-500 hover:bg-indigo-700 rounded-lg text-white">Update Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
