<?php

namespace App\Services;

use App\Models\LandingPageConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketPdfGeneratorService
{
    public function generateAll($registrant, $order)
    {
        $tickets = collect();

        // Tiket utama
        $tickets->push([
            'name' => $registrant->name,
            'ticket' => $registrant->ticket,
        ]);

        // Tiket peserta tambahan (attendees)
        foreach ($registrant->attendees as $attendee) {
            $tickets->push([
                'name' => $attendee->name,
                'ticket' => $attendee->ticket,
            ]);
        }

        Log::info('[PDF] Generating ' . $tickets->count() . ' ticket PDFs for order ' . $order->order_number);

        $attachments = [];

        foreach ($tickets as $index => $t) {
            if (!$t['ticket']) continue;

            // Generate QR Code
            $invoiceCode = strtoupper($order->order_number);
            $qrDir = storage_path('app/public/qrcodes');
            if (!file_exists($qrDir)) {
                mkdir($qrDir, 0755, true);
            }

            $qrPath = $qrDir . '/' . $invoiceCode . '.png';
            QrCode::format('png')
                ->size(250)
                ->margin(1)
                ->generate($invoiceCode, $qrPath);

            $landing = LandingPageConfig::first();

            // Render PDF dengan path QR 
            $pdf = Pdf::loadView('pdf.ticket', [
                'registrant' => $registrant,
                'order' => $order,
                'owner_name' => $t['name'],
                'ticket_title' => $t['ticket']->title,
                'ticket_price' => $t['ticket']->price,
                'ticket_code' => $t['ticket']->code,
                'qrPath' => $qrPath,

                'event_name' => $landing->event_name,
                'event_date' => $landing->event_date,
                'event_time_start' => $landing->event_time_start ? date('H:i', strtotime($landing->event_time_start)) : null,
                'event_time_end' => $landing->event_time_end ? date('H:i', strtotime($landing->event_time_end)) : null,
                'event_location' => $landing->event_location,
            ])->setPaper('a4', 'portrait');

            $pdfData = $pdf->output();

            // Simpan PDF ke storage publik 
            $fileName = sprintf(
                'E-Voucher-%s-%s.pdf',
                $order->order_number,
                str_replace(' ', '_', $t['name'])
            );

            $filePath = 'tickets/' . $fileName;

            Storage::disk('public')->put($filePath, $pdfData);

            $attachments[] = [
                'name' => $fileName,
                'data' => $pdfData,
                'path' => Storage::disk('public')->url($filePath),
            ];

            Log::info("[PDF] Saved ticket to public storage: {$filePath}");
        }

        return $attachments;
    }
}
