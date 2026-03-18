<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Services\TicketPdfGeneratorService;
use Illuminate\Support\Facades\Log;

class PaymentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $registrant;
    public $order;

    public function __construct($registrant, $order)
    {
        $this->registrant = $registrant;
        $this->order = $order;
    }

    public function build()
    {
        Log::info('[Mail] Building PaymentSuccessMail with split tickets', [
            'order_id' => $this->order->order_number,
        ]);

        $pdfService = new TicketPdfGeneratorService();
        $attachments = $pdfService->generateAll($this->registrant, $this->order);

        $email = $this->subject('Pembayaran Berhasil - ' . $this->order->order_number)
            ->view('emails.payment_success')
            ->with([
                'registrant' => $this->registrant,
                'order' => $this->order,
            ]);

        foreach ($attachments as $a) {
            $email->attachData($a['data'], $a['name'], ['mime' => 'application/pdf']);
        }

        return $email;
    }
}
