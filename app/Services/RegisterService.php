<?php

namespace App\Services;

use App\Models\{Registrant, Attendee, Ticket, Order};
use Illuminate\Support\Facades\DB;
use Midtrans\Snap;

class RegisterService
{
    public function register(array $payload)
    {
        return DB::transaction(function () use ($payload) {
            $registrantData = $payload['registrant'];
            $attendeesData = $payload['attendees'] ?? [];

            $hasAttendees = count($attendeesData) > 0;

            if (1 + count($attendeesData) > 4) {
                abort(422, "Maksimal 4 tiket per registrasi (termasuk registrant).");
            }

            // Hitung total harga dan tiket 
            $ticketsInvolved = [];

            // Menambahkan tiket registrant
            $registrantTicket = Ticket::lockForUpdate()->findOrFail($registrantData['ticket_id']);
            $ticketsInvolved[] = $registrantTicket;

            // Menambahkan tiket attendees jika ada
            if ($hasAttendees) {
                foreach ($attendeesData as $att) {
                    $ticket = Ticket::lockForUpdate()->findOrFail($att['ticket_id']);
                    $ticketsInvolved[] = $ticket;
                }
            }

            $totalCost = collect($ticketsInvolved)->sum('price');
            $totalTickets = count($ticketsInvolved);

            // Validasi stok tersisa 
            foreach ($ticketsInvolved as $t) {
                if ($t->remaining <= 0) {
                    abort(422, "Tiket {$t->title} sudah habis.");
                }
            }

            $registrant = Registrant::create([
                'unique_code' => 'JMF-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT),
                'ticket_id' => $registrantTicket->id,
                'name' => $registrantData['name'],
                'email' => $registrantData['email'],
                'phone' => $registrantData['phone'],
                'gender' => $registrantData['gender'] ?? null,
                'birthdate' => $registrantData['birthdate'] ?? null,
                'document' => $registrantData['document'] ?? null,
                'total_cost' => $totalCost,
                'total_tickets' => $totalTickets,
                'status' => 'pending',
            ]);

            //  Buat attendees (jika ada) 
            if ($hasAttendees) {
                foreach ($attendeesData as $att) {
                    Attendee::create([
                        'registrant_id' => $registrant->id,
                        'ticket_id' => $att['ticket_id'],
                        'name' => $att['name'],
                        'gender' => $att['gender'] ?? null,
                        'birthdate' => $att['birthdate'] ?? null,
                        'document' => $att['document'] ?? null,
                    ]);
                }
            }

            $order = Order::create([
                'registrant_id' => $registrant->id,
                'order_number' => 'JMF' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT),
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
                'item_details' => $this->mapTicketItems($ticketsInvolved),
                'callbacks' => [
                    'finish' => url("https://jayapuramusicfest.com/order/{$order->order_number}"),
                ],
            ];

            $snapToken = Snap::getSnapToken($midtransParams);
            $redirectUrl = "https://app.midtrans.com/snap/v2/vtweb/" . $snapToken;

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
                    'finish_redirect' => url("http://localhost:3000/order/{$order->order_number}"),
                ],
                'registrant' => [
                    'id' => $registrant->id,
                    'unique_code' => $registrant->unique_code,
                ],
            ];
        });
    }

    /**
     * Format item untuk Midtrans Snap
     */
    private function mapTicketItems($tickets)
    {
        return collect($tickets)->map(fn($ticket) => [
            'id' => $ticket->id,
            'price' => $ticket->price,
            'quantity' => 1,
            'name' => $ticket->title,
        ])->toArray();
    }
}
