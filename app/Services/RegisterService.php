<?php

namespace App\Services;

use App\Models\Registrant;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Midtrans\Snap;

class RegisterService
{
    /**
     * Harga fix untuk License Key.
     * bisa memindahkannya ke config atau database jika harganya dinamis.
     */
    private const LICENSE_PRICE = 150000;

    public function register(array $payload)
    {
        return DB::transaction(function () use ($payload) {
            $registrantData = $payload['registrant'];

            // Generate Serial Number (8-12 Karakter, Alphanumeric + ASCII)
            $serialNumber = $this->generateSerialNumber();
            $totalCost = self::LICENSE_PRICE;

            // Buat data pendaftar/pembeli lisensi
            $registrant = Registrant::create([
                // Kita ganti unique_code menjadi serial_number (pastikan sesuaikan di migration)
                'serial_number' => $serialNumber,
                'name' => $registrantData['name'],
                'email' => $registrantData['email'],
                'phone' => $registrantData['phone'],
                'total_cost' => $totalCost,
                'status' => 'pending', // Key belum aktif sebelum dibayar
            ]);

            // Buat Order untuk ditagihkan ke Midtrans
            $order = Order::create([
                'registrant_id' => $registrant->id,
                'order_number' => 'LIC-' . date('Ymd') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT),
                'amount' => $totalCost,
                'currency' => 'IDR',
                'payment_method' => 'midtrans_snap',
                'payment_status' => Order::STATUS_PENDING,
            ]);

            // Data Midtrans 
            $midtransParams = [
                'transaction_details' => [
                    'order_id' => $order->order_number,
                    'gross_amount' => $totalCost,
                ],
                'customer_details' => [
                    'first_name' => $registrant->name,
                    'email' => $registrant->email,
                    'phone' => $registrant->phone,
                ],
                'item_details' => [
                    [
                        'id' => 'APP-LIC-01',
                        'price' => $totalCost,
                        'quantity' => 1,
                        'name' => 'Desktop Application License Key',
                    ]
                ],
                'callbacks' => [
                    // redirect ke halaman sukses download/terima kasih
                    'finish' => url("http://localhost:3000/order/{$order->order_number}"),
                ],
            ];

            $snapToken = Snap::getSnapToken($midtransParams);

            // CEK ENVIRONMENT UNTUK URL SNAP
            $isProduction = config('midtrans.is_production', false);
            $snapBaseUrl = $isProduction
                ? "https://app.midtrans.com/snap/v2/vtweb/"
                : "https://app.sandbox.midtrans.com/snap/v2/vtweb/";

            $redirectUrl = $snapBaseUrl . $snapToken;

            $order->update([
                'midtrans_snap_token' => $snapToken,
            ]);

            return [
                'order' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'amount' => $order->amount,
                    'currency' => $order->currency,
                    'payment_status' => $order->payment_status,
                    'midtrans_snap_token' => $snapToken,
                    'redirect_url' => $redirectUrl,
                ],
                'registrant' => [
                    'id' => $registrant->id,
                ],
            ];
        });
    }

    /**
     * Generate Serial Number Acak
     * Panjang: 8 s/d 12 Karakter
     * Mengandung: Huruf Besar, Huruf Kecil, Angka, dan ASCII symbol
     */
    private function generateSerialNumber(): string
    {
        $length = mt_rand(8, 12);
        // Kumpulan karakter: Alphanumeric + ASCII character
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_=+<>?';
        $serialNumber = '';

        for ($i = 0; $i < $length; $i++) {
            $serialNumber .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $serialNumber;
    }
}
