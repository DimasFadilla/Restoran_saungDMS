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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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

    Log::info('Reservation data saved to session:', ['reservation' => $reservation->toArray()]);

    return to_route('reservations.step-two'); // Pengalihan ke step-two
}

   // Step 2: Memilih Meja & Opsi Memesan Menu
public function stepTwo(Request $request)
{
    Log::info('StepTwo method called');

    $reservation = $request->session()->get('reservation');
    if (!$reservation) {
        return redirect()->route('reservations.step-one')->with('error', 'Please complete the reservation form first.');
    }

    // Mendapatkan semua ID meja yang sudah dipesan pada tanggal dan waktu yang sama
    $res_table_ids = Reservation::where('res_date', $reservation->res_date)
                                ->where('status', '!=', 'cancelled') // Mengasumsikan ada status 'cancelled'
                                ->pluck('table_id');

    // Mendapatkan daftar meja yang tersedia
    $tables = Table::where('status', TableStatus::Avalaiable)
                   ->where('guest_number', '>=', $reservation->guest_number)
                   ->whereNotIn('id', $res_table_ids)
                   ->get();

    // Mengambil semua menu yang tersedia
    $menus = Menu::all();

    return view('reservations.step-two', compact('tables', 'reservation', 'menus'));
}
public function storeStepTwo(Request $request)
{
    $validated = $request->validate([
        'table_id' => ['required'],
        'order_menu' => ['nullable', 'boolean'], // Checkbox untuk memesan menu (opsional)
    ]);

    $reservation = $request->session()->get('reservation');
    $reservation->fill($validated);
    $reservation->table_id = $validated['table_id'];
    $reservation->status = 'pending'; // Set status default

    // Mendapatkan meja yang dipilih
    $table = Table::findOrFail($validated['table_id']);

    // Validasi jumlah tamu
    if ($reservation->guest_number > $table->guest_number) {
        return back()->with('warning', 'Please choose the table based on the number of guests.');
    }

    // Validasi tanggal reservasi
    $request_date = Carbon::parse($reservation->res_date);
    $existing_reservations = Reservation::where('table_id', $validated['table_id'])
                                        ->where('res_date', $request_date->format('Y-m-d'))
                                        ->get();

    foreach ($existing_reservations as $existing_reservation) {
        if ($existing_reservation->res_date->format('Y-m-d') == $request_date->format('Y-m-d')) {
            return back()->with('warning', 'This table is already reserved for this date.');
        }
    }

    // Simpan data reservasi ke database
    $reservation->save();

    // Cek jika pengguna ingin memesan menu
    if ($request->has('order_menu') && $request->input('order_menu') == 1) {
        // Jika pengguna ingin memesan menu, lanjut ke step-three
        return to_route('reservations.step-three');
    }

    // Jika pengguna tidak memesan menu, langsung ke step-four
    return to_route('reservations.step-four');
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
