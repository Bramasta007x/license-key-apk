<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>E-Voucher {{ $order->order_number }}</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            background: #f3f3f3;
            color: #222;
            margin: 0;
            padding: 20px;
        }

        .voucher {
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 25px 35px;
            width: 100%;
            max-width: 700px;
            margin: 0 auto;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            width: 110px;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 22px;
            color: #b20000;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 16px;
            font-weight: normal;
            color: #444;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            border-bottom: 2px solid #e0e0e0;
            margin-top: 25px;
            margin-bottom: 10px;
            text-transform: uppercase;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 6px 0;
            font-size: 13px;
            vertical-align: top;
        }

        td:first-child {
            width: 45%;
            color: #555;
        }

        td:last-child {
            color: #111;
            font-weight: 500;
        }

        .qr {
            text-align: center;
            margin-top: 30px;
        }

        .qr img {
            width: 140px;
            height: 140px;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 6px;
            background: #fff;
        }

        .footer {
            margin-top: 35px;
            font-size: 11px;
            color: #666;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            line-height: 1.4;
        }
    </style>
</head>

<body>
    <div class="voucher">
        {{-- HEADER --}}
        <div class="header">
            @if (file_exists(public_path('images/logo.png')))
                <img src="{{ public_path('images/logo.png') }}" alt="Logo">
            @endif
            <h1>E-Voucher</h1>
            <h2>{{ strtoupper($event_name ?? 'EVENT NAME') }}</h2>
        </div>

        {{-- INFORMASI PEMEGANG TIKET --}}
        <div class="section-title">Informasi Pemegang Tiket / Ticket Holder</div>
        <table>
            <tr>
                <td>Nama</td>
                <td>: {{ $owner_name ?? ($registrant->name ?? '-') }}</td>
            </tr>
            <tr>
                <td>Tipe Tiket</td>
                <td>: {{ $ticket_title ?? '-' }}</td>
            </tr>
            <tr>
                <td>Harga Tiket</td>
                <td>: Rp{{ number_format($ticket_price ?? 0, 0, ',', '.') }}</td>
            </tr>
        </table>

        {{-- INFORMASI PESANAN --}}
        <div class="section-title">Informasi Pesanan</div>
        <table>
            <tr>
                <td>Kode Tagihan</td>
                <td>: {{ strtoupper($order->order_number) }}</td>
            </tr>
            <tr>
                <td>Tanggal Pembelian</td>
                <td>: {{ $order->payment_time->format('d M Y') }}</td>
            </tr>
            <tr>
                <td>Status Pembayaran</td>
                <td>: {{ strtoupper($order->payment_status) }}</td>
            </tr>
            <tr>
                <td>Jumlah Pembayaran</td>
                <td>: Rp{{ number_format($order->amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Pemesan</td>
                <td>: {{ $registrant->name ?? '-' }}</td>
            </tr>
        </table>

        {{-- QR CODE --}}
        @if (isset($qrPath) && file_exists($qrPath))
            <div class="qr">
                <img src="{{ $qrPath }}" alt="QR Code">
                <p style="font-size: 12px; color: #666; margin-top: 8px;">
                    Tunjukkan QR Code ini saat check-in
                </p>
                <p style="font-size: 11px; color: #999;">
                    {{ strtoupper($order->order_number) }}
                </p>
            </div>
        @endif

        <div class="section-title">Detail Event</div>
        <table>
            <tr>
                <td>Nama Event</td>
                <td>: {{ $event_name }}</td>
            </tr>

            <tr>
                <td>Tanggal & Waktu</td>
                <td>:
                    {{ \Carbon\Carbon::parse($event_date)->translatedFormat('d F Y') }},
                    {{ $event_time_start }} – {{ $event_time_end }} WIT
                </td>
            </tr>

            <tr>
                <td>Lokasi</td>
                <td>: {{ $event_location }}</td>
            </tr>
        </table>

        {{-- SYARAT & KETENTUAN --}}
        <div class="section-title">Syarat & Ketentuan</div>
        <p style="font-size: 12px; line-height: 1.5; text-align: justify;">
            E-voucher ini berlaku untuk <strong>1 (satu) orang</strong> dan hanya dapat digunakan untuk acara yang
            tercantum di atas.
            Harap tunjukkan e-voucher (digital atau cetak) saat memasuki area acara. Nama pada voucher harus sesuai
            dengan identitas diri.
            Voucher ini tidak dapat diuangkan kembali dan tidak dapat dipindahtangankan tanpa konfirmasi resmi dari
            penyelenggara.
        </p>

        {{-- FOOTER --}}
        <div class="footer">
            Jayapura Music Fest © {{ date('Y') }}<br>
            jayapuramusicfest.com | support@e-ticket-jayapura.com
        </div>
    </div>
</body>

</html>
