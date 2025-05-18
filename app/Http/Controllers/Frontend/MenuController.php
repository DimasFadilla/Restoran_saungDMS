<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Category;
use App\Models\table;
use Endroid\QrCode\QrCode;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $categories = Category::with('menus')->get();
        $tables = Table::all();
        return view('menus.index', compact('categories', 'tables'));
    }

    public function addToCart(Request $request, $menuId)
{
    $menu = Menu::findOrFail($menuId);

    // Ambil keranjang dari session, jika tidak ada, buat array kosong
    $cart = session()->get('cart', []);

    // Jika menu sudah ada dalam keranjang, tambahkan jumlahnya
    if (isset($cart[$menuId])) {
        $cart[$menuId]['quantity']++;
    } else {
        $cart[$menuId] = [
            'name' => $menu->name,
            'price' => $menu->price,
            'quantity' => 1,
            'image' => $menu->image,
        ];
    }

    // Simpan kembali ke session
    session()->put('cart', $cart);

    return redirect()->route('menus.index')->with('success', 'Menu added to cart!');
}

    public function updateQuantity(Request $request, $menuId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Ambil menu dari keranjang
        $cart = session()->get('cart', []);

        // Update jumlah item yang dipesan
        if (isset($cart[$menuId])) {
            $cart[$menuId]['quantity'] = $request->quantity;
        }

        // Simpan kembali ke session
        session()->put('cart', $cart);
        return redirect()->route('menus.checkout');
    }

    public function checkout()
    {
        $cart = session()->get('cart', []);
        $tables = Table::all(); // Mengambil semua meja dari database
        return view('menus.checkout', compact('cart', 'tables'));
    }
    
    public function placeOrder(Request $request)
{
    // Validasi data diri pelanggan
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string',
        'phone' => 'required|string',
        'table_id' => 'required|integer',
        'note' => 'nullable|string',  
    ]);

    // Hitung total harga dari keranjang
    $cart = session()->get('cart');
    $totalPrice = 0;
    foreach ($cart as $item) {
        $totalPrice += $item['price'] * $item['quantity'];
    }

    // Simpan order ke database
    $order = Order::create([
        'customer_name' => $validated['name'],
    'phone' => $validated['phone'],
    'email' => $validated['email'],
    'table_id' => $validated['table_id'],
    'total_price' => $totalPrice,
    'note' => $validated['note'] ?? null,   
    ]);

    // Menyimpan menu yang dipesan ke tabel pivot
    foreach ($cart as $menuId => $item) {
        $order->menus()->attach($menuId, [
            'quantity' => $item['quantity'],
            'price' => $item['price'],
        ]);
    }

    // Redirect ke halaman pembayaran QRIS
    return redirect()->route('payment.qris', ['order' => $order->id]);
}


    
    public function removeFromCart($menuId)
{
    // Ambil keranjang dari session
    $cart = session()->get('cart', []);

    // Jika item ada di keranjang, hapus item tersebut
    if (isset($cart[$menuId])) {
        unset($cart[$menuId]);
    }

    // Simpan keranjang yang diperbarui ke session
    session()->put('cart', $cart);

    // Arahkan kembali ke halaman menu dengan pesan sukses
    return redirect()->route('menus.index')->with('success', 'Item removed from cart!');
}





private function calculateTotalPrice($cart)
{
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

}
