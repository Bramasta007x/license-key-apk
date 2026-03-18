<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
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
        Log::info('[Mail] Building PaymentSuccessMail for License Key', [
            'order_id' => $this->order->order_number,
        ]);

        return $this->subject('Pembayaran Berhasil - License Key Anda (' . $this->order->order_number . ')')
            ->view('emails.payment_success')
            ->with([
                'registrant' => $this->registrant,
                'order' => $this->order,
            ]);
    }
}