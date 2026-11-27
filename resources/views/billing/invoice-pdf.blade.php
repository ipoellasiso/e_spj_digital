<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $tagihan->no_invoice }}</title>
    <style>
        body { font-family: sans-serif; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #10b981; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #064e3b; }
        .info-table { width: 100%; margin-bottom: 30px; }
        .info-table td { padding: 8px; vertical-align: top; }
        .table-total { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table-total th, .table-total td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .table-total th { background: #f8fafc; }
        .status { font-weight: bold; color: {{ $tagihan->status === 'paid' ? '#047857' : '#b91c1c' }}; }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVOICE TAGIHAN</h1>
        <p>e-SPJ Sistem Digital</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="50%">
                <strong>Ditagihkan Kepada:</strong><br>
                {{ $tagihan->opd->nama }}<br>
                {{ $tagihan->opd->alamat ?? '-' }}
            </td>
            <td width="50%" align="right">
                <strong>No. Invoice:</strong> {{ $tagihan->no_invoice }}<br>
                <strong>Tanggal Terbit:</strong> {{ $tagihan->created_at->format('d/m/Y') }}<br>
                <strong>Status:</strong> <span class="status">{{ strtoupper($tagihan->status) }}</span>
            </td>
        </tr>
    </table>

    <table class="table-total">
        <thead>
            <tr>
                <th>Deskripsi Paket</th>
                <th>Periode</th>
                <th class="text-end">Total Tagihan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Langganan {{ $tagihan->paket->nama }}</td>
                <td>{{ $tagihan->periode_mulai->format('d/m/Y') }} s/d {{ $tagihan->periode_berakhir->format('d/m/Y') }}</td>
                <td>Rp {{ number_format($tagihan->total_bayar, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    
    <p style="margin-top: 50px; text-align: center; font-size: 12px; color: #777;">
        Terima kasih telah berlangganan e-SPJ.
    </p>
</body>
</html>