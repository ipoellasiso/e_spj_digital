<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Ekspor laporan ke Excel. Angka ditulis sebagai angka (bukan teks) agar bisa dijumlahkan di Excel. */
class LaporanExcel
{
    private const FORMAT_UANG = '#,##0.00';

    /**
     * @param  array{pemda: string, opd: string, judul: string, periode: string, berkas: string}  $meta
     * @param  array<int, array>  $baris  dari LaporanService::bkuBaris()
     * @param  array{pa: array, bendahara: array}  $ttd
     */
    public function bku(array $meta, array $baris, int $totalSen, array $ttd): StreamedResponse
    {
        $ss = new Spreadsheet;
        $ws = $ss->getActiveSheet();
        $ws->setTitle('BKU');
        $baris0 = $this->kepala($ws, $meta, 'G');

        $judul = ['No', 'Tanggal', 'No. SPJ', 'Uraian', 'Rekening Belanja', 'Sumber Dana', 'Jumlah (Rp)'];
        $this->header($ws, $baris0, $judul);

        $r = $baris0 + 1;
        foreach ($baris as $b) {
            $ws->setCellValue("A{$r}", $b['no']);
            $ws->setCellValue("B{$r}", $b['tanggal']);
            $ws->setCellValueExplicit("C{$r}", (string) $b['nomor'], DataType::TYPE_STRING);
            $ws->setCellValue("D{$r}", $b['uraian']);
            $ws->setCellValue("E{$r}", $b['rekening']);
            $ws->setCellValue("F{$r}", $b['sumber_dana']);
            $ws->setCellValue("G{$r}", $b['jumlah_sen'] / 100);
            $r++;
        }
        $akhirData = $r - 1;

        $ws->setCellValue("A{$r}", 'TOTAL');
        $ws->mergeCells("A{$r}:F{$r}");
        $ws->getStyle("A{$r}:G{$r}")->getFont()->setBold(true);
        $ws->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $ws->setCellValue("G{$r}", $totalSen / 100);
        $this->kotak($ws, "A{$baris0}:G{$r}");
        $ws->getStyle("G" . ($baris0 + 1) . ":G{$r}")->getNumberFormat()->setFormatCode(self::FORMAT_UANG);
        $ws->getStyle("A" . ($baris0 + 1) . ":C{$akhirData}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $ws->getStyle("D" . ($baris0 + 1) . ":E{$akhirData}")->getAlignment()->setWrapText(true);

        foreach (['A' => 6, 'B' => 12, 'C' => 10, 'D' => 48, 'E' => 42, 'F' => 14, 'G' => 18] as $k => $lebar) {
            $ws->getColumnDimension($k)->setWidth($lebar);
        }
        $ws->freezePane('A' . ($baris0 + 1));
        $this->tandaTangan($ws, $r + 2, 'B', 'F', $ttd);

        return $this->unduh($ss, $meta['berkas']);
    }

    /**
     * @param  array<int, array>  $baris  dari LaporanService::realisasi()['baris']
     * @param  array{pagu: int, final: int, proses: int, sisa: int, persen: float}  $total
     */
    public function realisasi(array $meta, array $baris, array $total, array $ttd): StreamedResponse
    {
        $ss = new Spreadsheet;
        $ws = $ss->getActiveSheet();
        $ws->setTitle('Realisasi');
        $baris0 = $this->kepala($ws, $meta, 'G');

        $this->header($ws, $baris0, ['Kode', 'Uraian', 'Pagu (Rp)', 'Realisasi (Rp)', 'Dalam Proses (Rp)', 'Sisa Pagu (Rp)', '% Realisasi']);

        $r = $baris0 + 1;
        foreach ($baris as $b) {
            $ws->setCellValueExplicit("A{$r}", $b['kode'], DataType::TYPE_STRING);
            $ws->setCellValue("B{$r}", $b['uraian']);
            $ws->setCellValue("C{$r}", $b['pagu'] / 100);
            $ws->setCellValue("D{$r}", $b['final'] / 100);
            $ws->setCellValue("E{$r}", $b['proses'] / 100);
            $ws->setCellValue("F{$r}", $b['sisa'] / 100);
            $ws->setCellValue("G{$r}", $b['persen']);

            $tebal = $b['level'] !== 'rekening';
            $ws->getStyle("A{$r}:G{$r}")->getFont()->setBold($tebal);
            if ($b['level'] === 'program' || $b['level'] === 'kegiatan') {
                $ws->getStyle("A{$r}:G{$r}")->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($b['level'] === 'program' ? 'D9E8DC' : 'EAF2EC');
            }
            $ws->getStyle("B{$r}")->getAlignment()->setIndent(['program' => 0, 'kegiatan' => 1, 'sub' => 2, 'rekening' => 3][$b['level']])->setWrapText(true);
            $r++;
        }

        $ws->setCellValue("A{$r}", 'TOTAL');
        $ws->mergeCells("A{$r}:B{$r}");
        $ws->setCellValue("C{$r}", $total['pagu'] / 100);
        $ws->setCellValue("D{$r}", $total['final'] / 100);
        $ws->setCellValue("E{$r}", $total['proses'] / 100);
        $ws->setCellValue("F{$r}", $total['sisa'] / 100);
        $ws->setCellValue("G{$r}", $total['persen']);
        $ws->getStyle("A{$r}:G{$r}")->getFont()->setBold(true);
        $ws->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $this->kotak($ws, "A{$baris0}:G{$r}");
        $ws->getStyle('C' . ($baris0 + 1) . ":F{$r}")->getNumberFormat()->setFormatCode(self::FORMAT_UANG);
        $ws->getStyle('G' . ($baris0 + 1) . ":G{$r}")->getNumberFormat()->setFormatCode('0.00"%"');

        foreach (['A' => 22, 'B' => 62, 'C' => 18, 'D' => 18, 'E' => 18, 'F' => 18, 'G' => 13] as $k => $lebar) {
            $ws->getColumnDimension($k)->setWidth($lebar);
        }
        $ws->freezePane('A' . ($baris0 + 1));
        $this->tandaTangan($ws, $r + 2, 'B', 'F', $ttd);

        return $this->unduh($ss, $meta['berkas']);
    }

    // ------------------------------------------------------------- internal

    /** Judul laporan di baris atas. Mengembalikan nomor baris judul kolom (header tabel). */
    private function kepala(Worksheet $ws, array $meta, string $kolomAkhir): int
    {
        $ws->setCellValue('A1', $meta['pemda']);
        $ws->setCellValue('A2', $meta['opd']);
        $ws->setCellValue('A3', $meta['judul']);
        $ws->setCellValue('A4', $meta['periode']);
        foreach ([1, 2, 3] as $r) {
            $ws->mergeCells("A{$r}:{$kolomAkhir}{$r}");
            $ws->getStyle("A{$r}")->getFont()->setBold(true);
            $ws->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $ws->getStyle('A3')->getFont()->setSize(13);
        $ws->mergeCells("A4:{$kolomAkhir}4");
        $ws->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return 6;
    }

    private function header(Worksheet $ws, int $baris, array $judul): void
    {
        foreach ($judul as $i => $teks) {
            $ws->setCellValue(chr(65 + $i) . $baris, $teks);
        }
        $rentang = 'A' . $baris . ':' . chr(64 + count($judul)) . $baris;
        $ws->getStyle($rentang)->getFont()->setBold(true);
        $ws->getStyle($rentang)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $ws->getStyle($rentang)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1F7A3F');
        $ws->getStyle($rentang)->getFont()->getColor()->setRGB('FFFFFF');
        $ws->getRowDimension($baris)->setRowHeight(24);
    }

    private function kotak(Worksheet $ws, string $rentang): void
    {
        $ws->getStyle($rentang)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    private function tandaTangan(Worksheet $ws, int $baris, string $kiri, string $kanan, array $ttd): void
    {
        $ws->setCellValue("{$kiri}{$baris}", 'Mengetahui,');
        $ws->setCellValue("{$kiri}" . ($baris + 1), 'Pengguna Anggaran');
        $ws->setCellValue("{$kanan}{$baris}", 'Bendahara Pengeluaran');
        $nama = $baris + 5;
        $ws->setCellValue("{$kiri}{$nama}", $ttd['pa']['nama'] ?: '...............................');
        $ws->setCellValue("{$kiri}" . ($nama + 1), $ttd['pa']['nip'] ? 'NIP. ' . $ttd['pa']['nip'] : '');
        $ws->setCellValue("{$kanan}{$nama}", $ttd['bendahara']['nama'] ?: '...............................');
        $ws->setCellValue("{$kanan}" . ($nama + 1), $ttd['bendahara']['nip'] ? 'NIP. ' . $ttd['bendahara']['nip'] : '');
        $ws->getStyle("{$kiri}{$nama}")->getFont()->setBold(true)->setUnderline(true);
        $ws->getStyle("{$kanan}{$nama}")->getFont()->setBold(true)->setUnderline(true);
    }

    private function unduh(Spreadsheet $ss, string $berkas): StreamedResponse
    {
        return response()->streamDownload(function () use ($ss) {
            (new Xlsx($ss))->save('php://output');
        }, $berkas, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}