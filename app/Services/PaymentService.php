<?php

namespace App\Services;

use App\Models\{Order, Payment, Ticket, TicketSalesDaily};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentSuccessMail;
use Illuminate\Support\Carbon;
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

            $order = Order::where('order_number', $orderId)->first();

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

            Log::info('[Webhook] Order updated', [
                'order_id' => $order->id,
                'payment_status' => $paymentStatus,
            ]);

            // Update registrant status
            $order->registrant->update([
                'status' => $paymentStatus,
            ]);

            Log::info('[Webhook] Registrant updated', [
                'registrant_id' => $order->registrant->id,
                'status' => $paymentStatus,
            ]);

            // Buat record payment
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $payload['gross_amount'] ?? $order->amount,
                'method' => $payload['payment_type'] ?? 'midtrans_snap',
                'status' => $paymentStatus,
                'transaction_id' => $transactionId,
                'raw_payload' => $payload,
            ]);

            Log::info('[Webhook] Payment record created', [
                'payment_id' => $payment->id,
            ]);

            // Update stok tiket dan summary harian jika pembayaran berhasil
            if ($paymentStatus === 'paid') {
                Log::info('[Webhook] Payment is paid, updating tickets and sending email');
                $this->updateTicketSales($order);
                $this->sendPaymentSuccessEmail($order);
            }

            Log::info('[Webhook] Payment status updated successfully', [
                'order_id' => $orderId,
                'status' => $paymentStatus,
                'transaction_id' => $transactionId,
            ]);

            return [
                'order_id' => $orderId,
                'status' => $paymentStatus,
            ];
        });
    }

    /**
     * Update stok tiket & summary harian
     */
    private function updateTicketSales(Order $order)
    {
        Log::info('[Ticket] Starting stock update', ['order_id' => $order->order_number]);

        // Load attendees dengan ticket
        $registrant = $order->registrant;
        $attendees = $order->registrant->attendees()->with('ticket')->get();

        $tickets = collect();

        if ($registrant->ticket) {
            $tickets->push($registrant->ticket);
        }

        foreach ($attendees as $attendee) {
            if ($attendee->ticket) {
                $tickets->push($attendee->ticket);
            } else {
                Log::warning('[Ticket] No ticket found for attendee', ['attendee_id' => $attendee->id]);
            }
        }

        Log::info('[Ticket] Tickets to process', [
            'count' => $tickets->count(),
            'ticket_ids' => $tickets->pluck('id'),
        ]);

        foreach ($tickets as $ticket) {
            $this->decrementTicketAndDaily($ticket);
        }

        Log::info('[Ticket] Stock update completed', [
            'order_id' => $order->order_number,
            'tickets_count' => $tickets->count(),
        ]);
    }

    private function decrementTicketAndDaily(Ticket $ticket)
    {
        Log::info('[Ticket] Processing ticket', [
            'ticket_id' => $ticket->id,
            'title' => $ticket->title,
            'remaining_before' => $ticket->remaining,
        ]);

        $ticket = Ticket::lockForUpdate()->find($ticket->id);

        if (!$ticket) {
            Log::error('[Ticket] Ticket not found when locking', ['ticket_id' => $ticket->id]);
            return;
        }

        if ($ticket->remaining <= 0) {
            Log::warning('[Ticket] Ticket already sold out', [
                'ticket_id' => $ticket->id,
                'remaining' => $ticket->remaining,
            ]);
            return;
        }

        $ticket->decrement('remaining', 1);
        $ticket->refresh();

        Log::info('[Ticket] Stock decremented', [
            'ticket_id' => $ticket->id,
            'remaining_after' => $ticket->remaining,
        ]);

        $today = Carbon::today()->toDateString();

        $daily = TicketSalesDaily::updateOrCreate([
            'ticket_id' => $ticket->id,
            'date' => $today,
        ]);

        $daily->sold = ($daily->sold ?? 0) + 1;
        $daily->save();

        Log::info('[Ticket] Daily sales updated', [
            'ticket_id' => $ticket->id,
            'date' => $today,
        ]);
    }

    /**
     * Kirim email sukses pembayaran
     */
    private function sendPaymentSuccessEmail(Order $order)
    {
        $registrant = $order->registrant;

        if (!$registrant || !$registrant->email) {
            Log::warning('[Email] Registrant email not found', ['order_id' => $order->order_number]);
            return;
        }

        Log::info('[Email] Attempting to send email', [
            'order_id' => $order->order_number,
            'email' => $registrant->email,
        ]);

        try {
            Mail::to($registrant->email)->send(new PaymentSuccessMail($registrant, $order));
            Log::info('[Email] Payment success email sent', [
                'order_id' => $order->order_number,
                'email' => $registrant->email,
            ]);

            // Kirim WhatsApp via Fonnte
            $this->sendTicketViaFonnte($registrant, $order);
        } catch (\Throwable $e) {
            Log::error('[Email] Failed to send confirmation', [
                'order_id' => $order->order_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Kirim tiket via WhatsApp menggunakan Fonnte
     */
    private function sendTicketViaFonnte($registrant, $order)
    {
        $client = new Client();
        $token = env('FONNTE_TOKEN');
        $phone = $registrant->phone;

        $pdfService = new \App\Services\TicketPdfGeneratorService();
        $attachments = $pdfService->generateAll($registrant, $order);
        $ticketUrl = $attachments[0]['path'] ?? null;

        if (!$ticketUrl) {
            Log::error('[Fonnte] Ticket URL is missing or not public', ['order_id' => $order->order_number]);
            return;
        }

        $captionMessage = "Hai {$registrant->name},\n\nPembayaran tiket {$order->order_number} telah berhasil. E-Ticket Anda terlampir di pesan ini.\n\nTunjukkan QR Code saat masuk ke lokasi acara.\nTerima kasih,\nJayapura Music Fest";
        $fileName = 'E-Ticket-' . $order->order_number . '.pdf';

        try {
            $response = $client->post('https://api.fonnte.com/send', [
                'headers' => [
                    'Authorization' => $token,
                ],
                'form_params' => [
                    'target' => $phone,
                    'url' => $ticketUrl, 
                    'filename' => $fileName, 
                    'message' => $captionMessage, 
                ],
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            $isSuccess = isset($responseData['status']) && (
                $responseData['status'] === true || 
                $responseData['status'] === 'true' ||
                $responseData['status'] === 'success'
            );

            if ($isSuccess) {
                Log::info('[Fonnte] WhatsApp (File) sent successfully', [
                    'order_id' => $order->order_number,
                    'phone' => $phone,
                    'response' => $responseData,
                ]);
            } else {
                Log::warning('[Fonnte] WhatsApp (File) failed to send (API Response)', [
                    'order_id' => $order->order_number,
                    'response' => $responseData,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[Fonnte] Failed to send WhatsApp (Exception)', [
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
        // Skip validation jika field tidak lengkap
        $required = ['order_id', 'status_code', 'gross_amount', 'signature_key'];
        foreach ($required as $key) {
            if (!isset($payload[$key])) {
                Log::warning('[Webhook] Missing required field for signature', ['field' => $key]);
                return false;
            }
        }

        $serverKey = config('midtrans.server_key');

        // Memastikan gross_amount tidak ada trailing .00
        $grossAmount = number_format((float)$payload['gross_amount'], 2, '.', '');
        $computed = hash('sha512', $payload['order_id'] . $payload['status_code'] . $grossAmount . $serverKey);

        $computed = hash('sha512', $payload['order_id'] . $payload['status_code'] . $grossAmount . $serverKey);

        $isValid = $computed === $payload['signature_key'];

        if (!$isValid) {
            Log::error('[Webhook] Invalid signature', [
                'order_id' => $payload['order_id'],
                'status_code' => $payload['status_code'],
                'gross_amount' => $grossAmount,
                'computed' => $computed,
                'received' => $payload['signature_key'],
            ]);
        } else {
            Log::info('[Webhook] Signature validation passed');
        }

        return $isValid;
    }
}
