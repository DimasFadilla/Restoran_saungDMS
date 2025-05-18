<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function qrisPayment($orderId)
    {
        $order = Order::findOrFail($orderId);

        // Pastikan order ditemukan
        if (!$order) {
            return redirect()->route('menus.index')->with('error', 'Order not found.');
        }

        // Data yang akan digunakan dalam QRIS (misalnya URL untuk pembayaran)
        $qrisData = "https://paymentgateway.com/qris?order_id={$order->id}&amount={$order->total_price}";

        // Membuat QR Code dengan library Endroid
        $qrCode = new QrCode($qrisData);

        // Menentukan path penyimpanan file gambar QRIS di folder public storage
        $qrisImagePath = storage_path('app/public/qris_images/qris_' . $orderId . '.png');

        // Membuat instance PngWriter
        $writer = new PngWriter();

        // Menulis QR code ke dalam string
        $qrCodeImageString = $writer->write($qrCode); // returns a string

        // Menyimpan gambar QRIS ke file menggunakan file_put_contents
        file_put_contents($qrisImagePath, $qrCodeImageString);

        // Mengembalikan path gambar ke view
        return view('menus.qrisPayment', compact('order', 'qrisImagePath'));
    }

    // Metode lainnya tetap sama
    public function checkPaymentStatus()
    {
        $paymentStatus = 'pending'; // Setel status sesuai dengan status pembayaran sebenarnya
        return view('menus.paymentStatus', compact('paymentStatus'));
    }

    // Metode untuk mengupload screenshot QRIS
    public function uploadQrisScreenshot(Request $request)
    {
        // Validasi file screenshot QRIS
        $request->validate([
            'qris_screenshot' => 'required|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        // Upload screenshot QRIS
        $path = $request->file('qris_screenshot')->store('qris_screenshots', 'public');

        // Ambil order yang sesuai dengan session
        $order = Order::findOrFail(session('order_id')); // Pastikan order_id disimpan di session

        // Update status pembayaran dan simpan screenshot
        $order->update([
            'qris_screenshot' => $path,
            'payment_status' => 'completed', // Set status menjadi 'completed'
        ]);

        // Redirect ke halaman status pembayaran
        return redirect()->route('payment.status')->with('success', 'Payment verification successful!');
    }

    // Validasi pembayaran oleh admin
    public function validatePayment($orderId)
    {
        $order = Order::findOrFail($orderId);

        // Ubah status pembayaran menjadi 'completed' jika validasi berhasil
        $order->update([
            'payment_status' => 'completed',
        ]);

        // Kembali ke halaman dengan pesan sukses
        return redirect()->route('admin.orders')->with('success', 'Payment successfully validated!');
    }

    // Menampilkan semua pesanan untuk admin
    public function showAdminOrders()
    {
        $orders = Order::all();  // Ambil semua pesanan
        return view('admin.orders', compact('orders'));
    }
}
