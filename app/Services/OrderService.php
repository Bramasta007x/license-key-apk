<?php

namespace App\Services;

use App\Models\Order;

class OrderService
{
    public function getOrderStatus(string $orderNumber)
    {
        $order = Order::with(['registrant'])->where('order_number', $orderNumber)->first();

        if (!$order) {
            return null;
        }

        $registrant = $order->registrant;

        $formattedPaymentMethod = $order->payment_channel
            ? strtoupper(str_replace(['_', '-'], ' ', $order->payment_channel))
            : '-';

        return [
            'order_number' => $order->order_number,
            'payment_method' => $formattedPaymentMethod,
            'payment_status' => $order->payment_status,
            'amount' => (int) $order->amount,
            'payment_time' => $order->payment_time,
            'registrant' => [
                'name' => $registrant->name,
                'email' => $registrant->email,
                'phone' => $registrant->phone,
                'serial_number' => $order->payment_status === 'paid' 
                    ? $registrant->serial_number 
                    : 'Menunggu Pembayaran...', 
            ]
        ];
    }
}