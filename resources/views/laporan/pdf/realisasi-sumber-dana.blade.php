<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Realisasi Sumber Dana</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h3, .header h4 { margin: 0; padding: 0; line-height: 1.2; }
        .header h3 { font-size: 14px; }
        .header h4 { font-size: 12px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #065f46; color: white; border: 1px solid #047857; padding: 6px 4px; text-align: center; font-weight: bold; }
        td { border: 1px solid #cbd5e1; padding: 5px 4px; vertical-align: middle; }
        
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        
        .tr-program { background-color: #eaf2ec; font-weight: bold; }
        .tr-kegiatan { background-color: #f8fafc; font-weight: bold; }
        .tr-sub { font-weight: bold; color: #047857; }
        .tr-rekening { color: #475569; }
        
        .ttd-table { width: 100%; margin-top: 30px; }
        .ttd-table td { border: none; text-align: center; vertical-align: bottom; height: 70px; }
    </style>
</head>
<body>
    <div class="header">
        <h3>PEMERINTAH KOTA PALU</h3>
        <h4>{{ strtoupper($opd->nama) }}</h4>
        <h4>REALISASI ANGGARAN BELANJA PER SUMBER DANA TAHUN {{ $tahun }}</h4>
        <p style="margin-top: 5px; font-size: 10px;">Sampai dengan {{ $sampai->translatedFormat('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Kode</th>
                <th style="width: 30%; text-align: left;">Uraian</th>
                <th style="width: 12%;">Pagu (Rp)</th>
                @foreach ($hasil['sumber_dana'] as $sd)
                    <th>{{ $sd->nama }}</th>
                @endforeach
                <th style="width: 6%;">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($hasil['baris'] as $b)
                @php
                    $trClass = 'tr-' . $b['level'];
                    $isRek = $b['level'] === 'rekening';
                @endphp
                <tr class="{{ $trClass }}">
                    <td class="text-center">{{ $b['kode'] }}</td>
                    <td style="{{ $isRek ? 'padding-left: 15px;' : '' }}">{{ $b['uraian'] }}</td>
                    <td class="text-end">{{ number_format($b['pagu']/100, 2, ',', '.') }}</td>
                    @foreach ($hasil['sumber_dana'] as $sd)
                        @php
                            $nilaiFinal = $b['dana'][$sd->id][0] ?? 0;
                            $nilaiProses = $b['dana'][$sd->id][1] ?? 0;
                            $totalDana = $nilaiFinal + $nilaiProses;
                        @endphp
                        <td class="text-end">
                            @if($totalDana > 0)
                                {{ number_format($nilaiFinal/100, 2, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                    <td class="text-end">{{ number_format($b['persen'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="fw-bold">
            <tr>
                <td colspan="2" class="text-end">TOTAL KESELURUHAN</td>
                <td class="text-end">{{ number_format($hasil['total']['pagu']/100, 2, ',', '.') }}</td>
                @foreach ($hasil['sumber_dana'] as $sd)
                    @php
                        $nilaiFinal = $hasil['total']['dana'][$sd->id][0] ?? 0;
                    @endphp
                    <td class="text-end">{{ number_format($nilaiFinal/100, 2, ',', '.') }}</td>
                @endforeach
                <td class="text-end">{{ number_format($hasil['total']['pagu'] > 0 ? (array_sum(array_column($hasil['total']['dana'], 0)) / $hasil['total']['pagu']) * 100 : 0, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="ttd-table">
        <tr>
            <td style="width: 50%;">
                Mengetahui,<br>
                Pengguna Anggaran<br><br><br><br>
                <strong><u>{{ $ttd['pa']['nama'] }}</u></strong><br>
                NIP. {{ $ttd['pa']['nip'] }}
            </td>
            <td style="width: 50%;">
                Palu, {{ $tanggal_cetak }}<br>
                Bendahara Pengeluaran<br><br><br><br>
                <strong><u>{{ $ttd['bendahara']['nama'] }}</u></strong><br>
                NIP. {{ $ttd['bendahara']['nip'] }}
            </td>
        </tr>
    </table>
</body>
</html>