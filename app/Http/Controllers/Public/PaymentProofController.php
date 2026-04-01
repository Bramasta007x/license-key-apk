<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentProofController extends Controller
{
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|string|exists:orders,order_number',
            'payment_proof' => 'required|image|max:2048',
            'recipient_account' => 'required|string',
        ]);

        $order = Order::where('order_number', $validated['order_number'])->first();

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Order already paid',
            ], 400);
        }

        $file = $validated['payment_proof'];
        $originalName = $file->getClientOriginalName();

        $path = $file->storeAs(
            'payment-proof',
            $originalName,
            'public'
        );

        $payment = Payment::create([
            'order_id' => $order->id,
            'amount' => $order->amount,
            'method' => 'manual_transfer',
            'status' => 'pending_verification',
            'payment_proof_url' => $path,
            'payment_recipient_account' => $validated['recipient_account'],
        ]);

        Log::info('[PaymentProof] Manual transfer proof uploaded', [
            'order_number' => $order->order_number,
            'payment_id' => $payment->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment proof uploaded successfully. Waiting for admin approval.',
            'data' => [
                'payment_id' => $payment->id,
                'payment_proof_url' => $path,
            ],
        ], 201);
    }
}
