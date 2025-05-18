<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\TableStatus;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Table;
use App\Models\Menu;
use App\Models\Order;
use Carbon\Carbon;
use App\Rules\DateBetween;
use App\Rules\TimeBetween;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function stepOne(Request $request)
    {
        $reservation = $request->session()->get('reservation');
        $min_date = Carbon::today();
        $max_date = Carbon::now()->addWeek();
        return view('reservations.step-one', compact('reservation', 'min_date', 'max_date'));
    }

    // storeStepOne - Pengalihan ke step-two
public function storeStepOne(Request $request)
{
    $validated = $request->validate([
        'first_name' => ['required'],
        'last_name' => ['required'],
        'email' => ['required', 'email'],
        'res_date' => ['required', 'date', new DateBetween, new TimeBetween],
        'tel_number' => ['required'],
        'guest_number' => ['required'],
    ]);

    $reservation = $request->session()->get('reservation', new Reservation());
    $reservation->fill($validated);
    $request->session()->put('reservation', $reservation);

    return to_route('reservations.step-two'); // Pengalihan ke step-two
}

   // Step 2: Memilih Meja & Opsi Memesan Menu
public function stepTwo(Request $request)
{
    $reservation = $request->session()->get('reservation');
    $res_table_ids = Reservation::orderBy('res_date')->get()->filter(function ($value) use ($reservation) {
        return $value->res_date->format('Y-m-d') == $reservation->res_date->format('Y-m-d');
    })->pluck('table_id');
    
    $tables = Table::where('status', TableStatus::Avalaiable)
        ->where('guest_number', '>=', $reservation->guest_number)
        ->whereNotIn('id', $res_table_ids)->get();

    // Check if the user wants to order menu
    if ($request->has('order_menu') && $request->input('order_menu') == 1) {
        // If the user wants to order menu, proceed to Step 3
        return to_route('reservations.step-three');
    }

    // If not ordering menu, go straight to Step 4 (payment)
    return to_route('reservations.step-four');
}

public function storeStepTwo(Request $request)
{
    $validated = $request->validate([
        'table_id' => ['required'],
        'menu_items' => ['nullable', 'array'], // Menu items are optional
        'total_cost' => ['nullable', 'numeric'] // Total cost is optional, depending on menu selection
    ]);

    $reservation = $request->session()->get('reservation');
    $reservation->fill($validated);
    $reservation->table_id = $validated['table_id'];
    $reservation->save();

    // Cek jika menu dipilih
    if ($request->has('menu_items') && !empty($request->input('menu_items'))) {
        $request->session()->put('menu_items', $request->input('menu_items')); // Menyimpan menu yang dipilih
        return to_route('reservations.step-three'); // Lanjut ke step 3 jika menu dipilih
    }

    // Jika tidak memilih menu, lanjutkan ke step 4
    return to_route('reservations.step-four'); // Langsung ke step 4 untuk pembayaran
}

// Step 3: Memilih Menu
public function stepThree(Request $request)
{
    $menus = Menu::all(); // Mengambil semua menu yang tersedia
    return view('reservations.step-three', compact('menus'));
}

// Handle selected menu items in Step 3
public function storeStepThree(Request $request)
{
    $validated = $request->validate([
        'menu_items' => ['required', 'array'], // Pastikan menu dipilih
    ]);

    $reservation = $request->session()->get('reservation');
    $order = new Order();

    // Isi data pesanan dengan data pelanggan
    $order->customer_name = $reservation->first_name . ' ' . $reservation->last_name;
    $order->phone = $reservation->tel_number;
    $order->email = $reservation->email;
    $order->table_id = $reservation->table_id;

    // Hitung total harga berdasarkan menu yang dipilih
    $total_price = Menu::whereIn('id', $validated['menu_items'])->sum('price');
    $order->total_price = $total_price;

    // Simpan pesanan
    $order->save();

    // Simpan menu yang dipilih di order
    $order->menus()->attach($validated['menu_items'], [
        'quantity' => 1,  // Jumlah default
        'price' => $total_price
    ]);

    return to_route('reservations.step-four'); // Lanjut ke step 4 untuk pembayaran
}

// Step 4: Pembayaran
public function stepFour(Request $request)
{
    $reservation = $request->session()->get('reservation');
    $order = Order::where('table_id', $reservation->table_id)->first(); // Ambil pesanan berdasarkan ID meja

    // Jika ada pesanan menu, tampilkan total harga dari menu yang dipilih
    $totalCost = $order ? $order->total_price : 0;

    // Jika tidak ada pesanan menu, hitung total berdasarkan harga meja
    if (!$order) {
        $totalCost = $reservation->guest_number * 50; // Misalnya biaya per orang atau total meja
    }

    return view('reservations.step-four', compact('totalCost', 'reservation', 'order'));
}


public function storeStepFour(Request $request)
{
    // Logika untuk menyelesaikan pembayaran
    // Integrasi dengan sistem pembayaran, misalnya Stripe atau Midtrans
    
    return to_route('thankyou'); // Redirect ke halaman ucapan terima kasih setelah pembayaran selesai
}

// Helper functions to calculate total price based on menu items selected
protected function calculateTotalPrice($menuItems)
{
    $menus = Menu::whereIn('id', $menuItems)->get();
    return $menus->sum('price');
}

protected function calculateMenuPrice($menuItems)
{
    $menus = Menu::whereIn('id', $menuItems)->get();
    return $menus->sum('price');
}




    
}
