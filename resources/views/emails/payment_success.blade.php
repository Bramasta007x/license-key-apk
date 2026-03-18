<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pembayaran Berhasil</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
  <h2>Hai {{ $registrant->name }},</h2>
  <p>Terima kasih atas pembelian Anda untuk <strong>{{ $order->order_number }}</strong>.</p>
  <p>
    Pembayaran sebesar <strong>Rp{{ number_format($order->amount, 0, ',', '.') }}</strong> telah berhasil.
  </p>
  <p>
    E-voucher acara telah dilampirkan pada email ini dalam format PDF.<br>
    Harap tunjukkan saat masuk ke lokasi acara.
  </p>
  <br>
  <p>Salam hangat,<br><strong>Tim Jayapura Music Fest</strong></p>
</body>
</html>
