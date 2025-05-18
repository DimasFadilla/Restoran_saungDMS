<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Menu;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    // Menampilkan semua pesanan
    public function index()
    {
        $orders = Order::all();  // Ambil semua pesanan dari database
        return view('admin.orders.index', compact('orders'));
    }

    public function create()
    {
        $menus = Menu::all(); // Ambil semua menu
        return view('admin.orders.create', compact('menus'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',
            'menu_id' => 'required|exists:menus,id',
            'quantity' => 'required|integer|min:1',
            'total_price' => 'required|numeric',
            'payment_status' => 'required|string',
        ]);

        // Simpan order ke database
        $order = Order::create([
            'customer_name' => $request->customer_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'menu_id' => $request->menu_id,
            'quantity' => $request->quantity,
            'total_price' => $request->total_price,
            'payment_status' => $request->payment_status,
        ]);

        return redirect()->route('admin.orders.index')->with('success', 'Order created successfully!');
    }

    // Menampilkan form untuk mengedit pesanan
    public function edit(Order $order)
    {
        return view('admin.orders.edit', compact('order'));
    }

    // Memperbarui pesanan
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|string',
        ]);

        // Update status pembayaran
        $order->update([
            'payment_status' => $request->payment_status,
        ]);

        return redirect()->route('admin.orders.index')->with('success', 'Order updated successfully.');
    }


    // Menghapus pesanan
    public function destroy(Order $order)
    {
        $order->delete();  // Hapus pesanan dari database

        return redirect()->route('admin.orders.index')->with('success', 'Order deleted successfully.');
    }
}
