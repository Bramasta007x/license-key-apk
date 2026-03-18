<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pembayaran Berhasil - License Key Anda</title>
  <style>
    body {
      font-family: 'Helvetica Neue', Arial, sans-serif;
      color: #333;
      line-height: 1.6;
      background-color: #f9f9f9;
      padding: 20px;
    }
    .container {
      max-width: 600px;
      margin: 0 auto;
      background-color: #ffffff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .header {
      text-align: center;
      border-bottom: 2px solid #eee;
      padding-bottom: 20px;
      margin-bottom: 20px;
    }
    .header h2 {
      margin: 0;
      color: #28a745;
    }
    .license-box {
      background-color: #f8f9fa;
      border: 2px dashed #007bff;
      padding: 20px;
      text-align: center;
      border-radius: 8px;
      margin: 25px 0;
    }
    .license-label {
      font-size: 14px;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 10px;
      display: block;
    }
    .license-key {
      font-size: 28px;
      font-weight: bold;
      color: #007bff;
      font-family: 'Courier New', Courier, monospace;
      letter-spacing: 2px;
      word-break: break-all;
    }
    .instructions {
      background-color: #e9ecef;
      padding: 15px;
      border-radius: 6px;
      font-size: 14px;
    }
    .footer {
      font-size: 12px;
      color: #999;
      text-align: center;
      border-top: 1px solid #eee;
      padding-top: 20px;
      margin-top: 30px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h2>Pembayaran Berhasil!</h2>
    </div>
    
    <p>Hai <strong>{{ $registrant->name }}</strong>,</p>
    <p>Terima kasih atas pembelian Anda. Pembayaran untuk nomor order <strong>{{ $order->order_number }}</strong> sebesar <strong>Rp{{ number_format($order->amount, 0, ',', '.') }}</strong> telah berhasil kami terima.</p>
    
    <p>Berikut adalah <strong>License Key</strong> Anda untuk mengaktifkan aplikasi desktop:</p>
    
    <div class="license-box">
      <span class="license-label">Serial Number / License Key</span>
      <span class="license-key">{{ $registrant->serial_number }}</span>
    </div>
    
    <div class="instructions">
      <p style="margin-top: 0; font-weight: bold;">Cara Penggunaan:</p>
      <ol style="margin-bottom: 0; padding-left: 20px;">
        <li>Buka aplikasi desktop (<strong>Noc.Exe</strong>) di komputer Anda.</li>
        <li>Masukkan <i>License Key</i> di atas ke dalam form aktivasi yang tersedia.</li>
        <li>Pastikan komputer Anda terhubung dengan internet saat proses verifikasi lisensi berlangsung.</li>
      </ol>
    </div>
    
    <p style="margin-top: 25px;">Simpan email ini dengan baik sebagai bukti kepemilikan lisensi Anda.</p>
    
    <p>Salam hangat,<br><strong>Tim Support License Manager</strong></p>

    <div class="footer">
      <p>Pesan ini dihasilkan secara otomatis. Jika Anda mengalami kendala, silakan hubungi layanan pelanggan kami.</p>
    </div>
  </div>
</body>
</html>