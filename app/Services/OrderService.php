<?php

namespace App\Services;

use App\Models\Order;

class OrderService
{
    public function getOrderStatus(string $orderNumber)
    {

        $order = Order::with([
            'registrant.ticket',
            'registrant.attendees.ticket',
        ])->where('order_number', $orderNumber)->first();

        if (!$order) {

            return null;
        }

        $registrant = $order->registrant;

        $formattedPaymentMethod = $order->payment_channel
            ? ucwords(str_replace(['_', '-'], ' ', $order->payment_channel))
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
                'gender' => $registrant->gender,
                'birthdate' => $registrant->birthdate,
                'ticket_title' => $registrant->ticket->title ?? null, 
                'ticklet_type' => $registrant->ticket->code ?? null
            ],
            'attendees' => $registrant->attendees->map(function ($att) {
                return [
                    'name' => $att->name,
                    'gender' => $att->gender,
                    'birthdate' => $att->birthdate,
                    'ticket_title' => $att->ticket->title ?? null,
                    'ticklet_type' => $att->ticket->code ?? null,
                    'document' => $att->document ?? null,
                ];
            })->values(),
        ];
    }
}
