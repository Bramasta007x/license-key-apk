<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentApprovalController extends Controller
{
    public function listPendingPayments(Request $request)
    {
        $payments = Payment::with(['order.registrant'])
            ->where('method', 'manual_transfer')
            ->where('status', 'pending_verification')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    public function approve(Request $request)
    {
        $validated = $request->validate([
            'payment_id' => 'required|uuid|exists:payments,id',
            'notes' => 'nullable|string',
        ]);

        $payment = Payment::with(['order.registrant'])->find($validated['payment_id']);

        if (! $payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        if ($payment->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Payment already approved',
            ], 400);
        }

        if ($payment->method !== 'manual_transfer') {
            return response()->json([
                'success' => false,
                'message' => 'Only manual transfer payments can be approved',
            ], 400);
        }

        $admin = $request->user();

        return DB::transaction(function () use ($payment, $admin, $validated) {
            $payment->update([
                'status' => 'paid',
                'approved_at' => now(),
                'approved_by' => $admin->id,
                'admin_notes' => $validated['notes'] ?? null,
            ]);

            $order = $payment->order;
            $order->update([
                'payment_status' => 'paid',
                'payment_time' => now(),
                'payment_channel' => 'manual_transfer',
            ]);

            $registrant = $order->registrant;
            if ($registrant) {
                $registrant->update(['status' => 'paid']);
            }

            Log::info('[PaymentApproval] Manual transfer approved', [
                'payment_id' => $payment->id,
                'order_number' => $order->order_number,
                'admin_id' => $admin->id,
            ]);

            $this->sendLicenseKey($order);

            return response()->json([
                'success' => true,
                'message' => 'Payment approved and license key sent to customer',
            ]);
        });
    }

    public function reject(Request $request)
    {
        $validated = $request->validate([
            'payment_id' => 'required|uuid|exists:payments,id',
            'reason' => 'required|string',
        ]);

        $payment = Payment::with(['order.registrant'])->find($validated['payment_id']);

        if (! $payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        if ($payment->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Payment already approved',
            ], 400);
        }

        if ($payment->status === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Payment already rejected',
            ], 400);
        }

        $admin = $request->user();

        $payment->update([
            'status' => 'rejected',
            'approved_at' => now(),
            'approved_by' => $admin->id,
            'admin_notes' => $validated['reason'],
        ]);

        Log::info('[PaymentApproval] Manual transfer rejected', [
            'payment_id' => $payment->id,
            'order_number' => $payment->order->order_number,
            'admin_id' => $admin->id,
            'reason' => $validated['reason'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment rejected',
        ]);
    }

    private function sendLicenseKey(Order $order)
    {
        $registrant = $order->registrant;

        if (! $registrant) {
            Log::warning('[PaymentApproval] Registrant not found', [
                'order_number' => $order->order_number,
            ]);

            return;
        }

        try {
            $mailClass = new \App\Mail\PaymentSuccessMail($registrant, $order);
            Mail::to($registrant->email)->send($mailClass);

            Log::info('[PaymentApproval] License key email sent', [
                'order_number' => $order->order_number,
                'email' => $registrant->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('[PaymentApproval] Failed to send license key email', [
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }

        $this->sendLicenseKeyViaFonnte($registrant, $order);
    }

    private function sendLicenseKeyViaFonnte($registrant, $order)
    {
        $client = new \GuzzleHttp\Client;
        $token = env('FONNTE_TOKEN');
        $phone = $registrant->phone;

        if (! $token || ! $phone) {
            Log::warning('[PaymentApproval] Fonnte token or phone missing', [
                'order_number' => $order->order_number,
            ]);

            return;
        }

        $message = "Hai {$registrant->name},\n\n";
        $message .= "Terima kasih! Pembayaran lisensi dengan nomor order *{$order->order_number}* telah dikonfirmasi.\n\n";
        $message .= "Berikut adalah *License Key* Anda:\n";
        $message .= "====================\n";
        $message .= "{$registrant->serial_number}\n";
        $message .= "====================\n\n";
        $message .= "Gunakan key di atas pada aplikasi Noc.Exe untuk proses verifikasi dan instalasi.\n";
        $message .= 'Harap simpan key ini dengan baik.';

        try {
            $client->post('https://api.fonnte.com/send', [
                'headers' => ['Authorization' => $token],
                'form_params' => [
                    'target' => $phone,
                    'message' => $message,
                ],
            ]);

            Log::info('[PaymentApproval] WhatsApp License Key sent', [
                'order_number' => $order->order_number,
                'phone' => $phone,
            ]);
        } catch (\Throwable $e) {
            Log::error('[PaymentApproval] Failed to send WhatsApp License Key', [
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
