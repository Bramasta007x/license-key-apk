<?php

namespace App\Services;

use App\Models\{Order, Payment};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentSuccessMail;
use GuzzleHttp\Client;

class PaymentService
{
    /**
     * Handle webhook callback from Midtrans
     */
    public function handleMidtransWebhook(array $payload)
    {
        return DB::transaction(function () use ($payload) {
            $orderId = $payload['order_id'] ?? null;
            $transactionStatus = $payload['transaction_status'] ?? null;
            $transactionId = $payload['transaction_id'] ?? null;

            Log::info('[Webhook] Processing payment', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
                'transaction_id' => $transactionId,
            ]);

            if (!$orderId || !$transactionStatus) {
                throw new \Exception('Missing order_id or transaction_status');
            }

            // Validasi signature Midtrans (skip untuk local testing)
            if (!$this->validateSignature($payload)) {
                if (config('app.env') === 'local') {
                    Log::warning('[Webhook] Invalid signature ignored in local env');
                } else {
                    Log::error('[Webhook] Invalid signature key');
                    return [
                        'order_id' => $orderId,
                        'status' => 'invalid_signature',
                    ];
                }
            }

            $order = Order::with('registrant')->where('order_number', $orderId)->first();

            if (!$order) {
                Log::warning('[Webhook] Order not found', ['order_id' => $orderId]);
                return [
                    'order_id' => $orderId,
                    'status' => 'not_found',
                ];
            }

            // Cek apakah sudah diproses sebelumnya
            if ($order->payment_status === 'paid') {
                Log::info('[Webhook] Order already paid', ['order_id' => $orderId]);
                return [
                    'order_id' => $orderId,
                    'status' => 'already_paid',
                ];
            }

            // Map status dari Midtrans
            $statusMap = [
                'capture' => 'paid',
                'settlement' => 'paid',
                'pending' => 'pending',
                'deny' => 'failed',
                'expire' => 'expired',
                'cancel' => 'cancelled',
                'failed' => 'failed',
            ];

            $paymentStatus = $statusMap[$transactionStatus] ?? 'pending';

            Log::info('[Webhook] Mapped status', [
                'midtrans_status' => $transactionStatus,
                'our_status' => $paymentStatus,
            ]);

            // Update order
            $order->update([
                'payment_status' => $paymentStatus,
                'midtrans_transaction_id' => $transactionId,
                'payment_time' => in_array($paymentStatus, ['paid']) ? now() : null,
                'payment_channel' => $payload['payment_type'] ?? null,
            ]);

            // Update registrant (License) status
            if ($order->registrant) {
                $order->registrant->update([
                    'status' => $paymentStatus,
                ]);
            }

            // Buat record payment
            Payment::create([
                'order_id' => $order->id,
                'amount' => $payload['gross_amount'] ?? $order->amount,
                'method' => $payload['payment_type'] ?? 'midtrans_snap',
                'status' => $paymentStatus,
                'transaction_id' => $transactionId,
                'raw_payload' => $payload,
            ]);

            // Jika pembayaran berhasil (Lunas), kirim Serial Number via Email dan WA
            if ($paymentStatus === 'paid' && $order->registrant) {
                Log::info('[Webhook] Payment is paid, sending License Key email and WA');
                $this->sendPaymentSuccessEmail($order);
                $this->sendLicenseKeyViaFonnte($order->registrant, $order);
            }

            return [
                'order_id' => $orderId,
                'status' => $paymentStatus,
            ];
        });
    }

    /**
     * Kirim email sukses pembayaran berisi Serial Number
     */
    private function sendPaymentSuccessEmail(Order $order)
    {
        $registrant = $order->registrant;

        if (!$registrant || !$registrant->email) {
            Log::warning('[Email] Registrant email not found', ['order_id' => $order->order_number]);
            return;
        }

        try {
            Mail::to($registrant->email)->send(new PaymentSuccessMail($registrant, $order));
            
            Log::info('[Email] License Key email sent', [
                'order_id' => $order->order_number,
                'email' => $registrant->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('[Email] Failed to send License Key confirmation', [
                'order_id' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Kirim Serial Number via WhatsApp menggunakan Fonnte
     */
    private function sendLicenseKeyViaFonnte($registrant, $order)
    {
        $client = new Client();
        $token = env('FONNTE_TOKEN');
        $phone = $registrant->phone;

        if (!$token || !$phone) {
            Log::warning('[Fonnte] Token or phone number missing', ['order_id' => $order->order_number]);
            return;
        }

        $message = "Hai {$registrant->name},\n\n";
        $message .= "Terima kasih! Pembayaran lisensi dengan nomor order *{$order->order_number}* telah berhasil.\n\n";
        $message .= "Berikut adalah *License Key* Anda:\n";
        $message .= "====================\n";
        $message .= "{$registrant->serial_number}\n";
        $message .= "====================\n\n";
        $message .= "Gunakan key di atas pada aplikasi Noc.Exe untuk proses verifikasi dan instalasi.\n";
        $message .= "Harap simpan key ini dengan baik.";

        try {
            $response = $client->post('https://api.fonnte.com/send', [
                'headers' => [
                    'Authorization' => $token,
                ],
                'form_params' => [
                    'target' => $phone,
                    'message' => $message, 
                ],
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            $isSuccess = isset($responseData['status']) && in_array((string)$responseData['status'], ['true', '1', 'success'], true);

            if ($isSuccess) {
                Log::info('[Fonnte] WhatsApp License Key sent successfully', [
                    'order_id' => $order->order_number,
                    'phone' => $phone,
                ]);
            } else {
                Log::warning('[Fonnte] WhatsApp License Key failed to send (API Response)', [
                    'order_id' => $order->order_number,
                    'response' => $responseData,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[Fonnte] Failed to send WhatsApp License Key (Exception)', [
                'order_id' => $order->order_number,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Validasi signature Midtrans
     */
    private function validateSignature(array $payload): bool
    {
        $required = ['order_id', 'status_code', 'gross_amount', 'signature_key'];
        foreach ($required as $key) {
            if (!isset($payload[$key])) {
                Log::warning('[Webhook] Missing required field for signature', ['field' => $key]);
                return false;
            }
        }

        $serverKey = config('midtrans.server_key');
        $grossAmount = number_format((float)$payload['gross_amount'], 2, '.', '');
        
        $computed = hash('sha512', $payload['order_id'] . $payload['status_code'] . $grossAmount . $serverKey);
        $isValid = $computed === $payload['signature_key'];

        if (!$isValid) {
            Log::error('[Webhook] Invalid signature', [
                'order_id' => $payload['order_id'],
                'computed' => $computed,
                'received' => $payload['signature_key'],
            ]);
        } else {
            Log::info('[Webhook] Signature validation passed');
        }

        return $isValid;
    }
}