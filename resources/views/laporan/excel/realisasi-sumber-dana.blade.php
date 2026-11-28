<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Realisasi Sumber Dana</title>
    <style>
        table { border-collapse: collapse; font-family: Arial, sans-serif; font-size: 11px; }
        th { background-color: #065f46; color: #ffffff; font-weight: bold; border: 1px solid #047857; padding: 5px; }
        td { border: 1px solid #cccccc; padding: 5px; vertical-align: middle; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .tr-program { background-color: #eaf2ec; font-weight: bold; }
        .tr-kegiatan { background-color: #f8fafc; font-weight: bold; }
        .tr-sub { font-weight: bold; color: #047857; }
    </style>
</head>
<body>
    <h3 style="text-align:center; margin-bottom:2px;">PEMERINTAH KOTA PALU</h3>
    <h4 style="text-align:center; margin-top:0; margin-bottom:2px;">{{ strtoupper($opd->nama) }}</h4>
    <h4 style="text-align:center; margin-top:0; margin-bottom:2px;">REALISASI ANGGARAN BELANJA PER SUMBER DANA TAHUN {{ $tahun }}</h4>
    <p style="text-align:center; font-size:12px; margin-top:0;">Sampai dengan {{ $sampai->translatedFormat('d F Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Uraian</th>
                <th>Pagu (Rp)</th>
                @foreach ($hasil['sumber_dana'] as $sd)
                    <th>{{ $sd->nama }}</th>
                @endforeach
                <th>% Realisasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($hasil['baris'] as $b)
                @php
                    $trClass = 'tr-' . $b['level'];
                    $isRek = $b['level'] === 'rekening';
                @endphp
                <tr class="{{ $trClass }}">
                    <td>{{ $b['kode'] }}</td>
                    <td style="{{ $isRek ? 'padding-left: 20px;' : '' }}">{{ $b['uraian'] }}</td>
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
                    <td class="text-end">{{ number_format($b['persen'], 2, ',', '.') }}%</td>
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
                <td class="text-end">{{ number_format($hasil['total']['pagu'] > 0 ? (array_sum(array_column($hasil['total']['dana'], 0)) / $hasil['total']['pagu']) * 100 : 0, 2, ',', '.') }}%</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>