<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Registrant;
use Illuminate\Support\Facades\DB;
use Midtrans\Snap;

class RegisterService
{
    private const FALLBACK_PRICE = 15000000;

    private const PAYMENT_METHOD_MIDTRANS = 'midtrans';

    private const PAYMENT_METHOD_MANUAL = 'manual_transfer';

    public function register(array $payload)
    {
        return DB::transaction(function () use ($payload) {
            $registrantData = $payload['registrant'];
            $paymentMethod = $payload['payment_method'] ?? self::PAYMENT_METHOD_MIDTRANS;

            $serialNumber = $this->generateSerialNumber();

            // Use total_cost from frontend (already includes PPN)
            $totalCost = (int) $payload['total_cost'];

            $registrant = Registrant::create([
                'serial_number' => $serialNumber,
                'name' => $registrantData['name'],
                'email' => $registrantData['email'],
                'phone' => $registrantData['phone'],
                'total_cost' => $totalCost,
                'status' => 'pending',
            ]);

            $order = Order::create([
                'registrant_id' => $registrant->id,
                'order_number' => 'LIC-'.date('Ymd').'-'.str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT),
                'amount' => $totalCost,
                'currency' => 'IDR',
                'payment_method' => $paymentMethod,
                'payment_status' => Order::STATUS_PENDING,
            ]);

            $response = [
                'order' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'amount' => $order->amount,
                    'currency' => $order->currency,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $order->payment_status,
                ],
                'registrant' => [
                    'id' => $registrant->id,
                ],
            ];

            if ($paymentMethod === self::PAYMENT_METHOD_MIDTRANS) {
                $midtransData = $this->createMidtransTransaction($order, $registrant);
                $response['order']['midtrans_snap_token'] = $midtransData['snap_token'];
                $response['order']['redirect_url'] = $midtransData['redirect_url'];
                $order->update(['midtrans_snap_token' => $midtransData['snap_token']]);
            } elseif ($paymentMethod === self::PAYMENT_METHOD_MANUAL) {
                $response['order']['payment_instructions'] = $this->getPaymentInstructions($totalCost);
            }

            return $response;
        });
    }

    private function createMidtransTransaction(Order $order, Registrant $registrant): array
    {
        $midtransParams = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => $order->amount,
            ],
            'customer_details' => [
                'first_name' => $registrant->name,
                'email' => $registrant->email,
                'phone' => $registrant->phone,
            ],
            'item_details' => [
                [
                    'id' => 'APP-LIC-01',
                    'price' => $order->amount,
                    'quantity' => 1,
                    'name' => 'Desktop Application License Key',
                ],
            ],
            'callbacks' => [
                'finish' => url("http://localhost:3000/order/{$order->order_number}"),
            ],
        ];

        $snapToken = Snap::getSnapToken($midtransParams);

        $isProduction = config('midtrans.is_production', false);
        $snapBaseUrl = $isProduction
            ? 'https://app.midtrans.com/snap/v2/vtweb/'
            : 'https://app.sandbox.midtrans.com/snap/v2/vtweb/';

        return [
            'snap_token' => $snapToken,
            'redirect_url' => $snapBaseUrl.$snapToken,
        ];
    }

    public function getPaymentInstructions(int $amount = null): array
    {
        return [
            'bank_accounts' => config('midtrans.bank_accounts', []),
            'instructions' => [
                'Silakan transfer sesuai jumlah yang tertera.',
                'Transfer ke salah satu rekening yang tersedia.',
                'Simpan bukti transfer Anda.',
                'Upload bukti transfer melalui halaman pembayaran.',
                'Tunggu konfirmasi dari admin (1x24 jam).',
            ],
            'amount' => $amount ?? self::FALLBACK_PRICE,
        ];
    }

    private function generateSerialNumber(): string
    {
        $length = mt_rand(8, 12);
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_=+<>?';
        $serialNumber = '';

        for ($i = 0; $i < $length; $i++) {
            $serialNumber .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $serialNumber;
    }
}
