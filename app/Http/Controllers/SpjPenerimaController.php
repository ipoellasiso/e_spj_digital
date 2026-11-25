<?php

namespace App\Http\Controllers;

use App\Imports\LembarPertama;
use App\Services\PenerimaParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/** Impor daftar penerima honor dari Excel ke formulir SPJ (belum disimpan; hasilnya diisikan ke formulir). */
class SpjPenerimaController extends Controller
{
    public function __construct(private PenerimaParser $parser)
    {
    }

    /** Membaca file dan mengembalikan baris penerima sebagai JSON untuk diisikan ke formulir. */
    public function impor(Request $request): JsonResponse
    {
        $request->validate(
            ['berkas' => ['required', 'file', 'mimes:xlsx,xls', 'max:2048']],
            ['berkas.required' => 'Pilih file Excel.', 'berkas.mimes' => 'File harus berformat .xlsx atau .xls.', 'berkas.max' => 'Ukuran file maksimal 2 MB.']
        );

        try {
            $sheets = Excel::toArray(new LembarPertama, $request->file('berkas'));
        } catch (Throwable $e) {
            report($e);

            return response()->json(['penerima' => [], 'galat' => ['File tidak bisa dibaca. Pastikan berupa file Excel yang valid.']], 422);
        }

        return response()->json($this->parser->parse($sheets[0] ?? []));
    }

    /** Unduh templat Excel kosong dengan contoh dua baris. */
    public function templat(): StreamedResponse
    {
        $ss = new Spreadsheet;
        $ws = $ss->getActiveSheet();
        $ws->setTitle('Penerima');
        $ws->fromArray([
            ['Nama Penerima', 'Jabatan', 'Honor (Rp)', 'Nama Bank', 'No. Rek', 'Nilai Pajak (Rp)'],
            ['Contoh Nama Satu', 'Perumus Kelurahan', 7500000, 'Bank Contoh', '1234567890', 375000],
            ['Contoh Nama Dua', 'Narasumber', 2000000, '', '', ''],
        ], null, 'A1');
        $ws->getStyle('A1:F1')->getFont()->setBold(true);
        foreach (['A' => 28, 'B' => 28, 'C' => 16, 'D' => 18, 'E' => 20, 'F' => 18] as $kolom => $lebar) {
            $ws->getColumnDimension($kolom)->setWidth($lebar);
        }
        // nomor rekening sebagai teks agar angka nol di depan tidak hilang
        $ws->getStyle('E2:E500')->getNumberFormat()->setFormatCode('@');
        $ws->getStyle('C2:C500')->getNumberFormat()->setFormatCode('#,##0');
        $ws->getStyle('F2:F500')->getNumberFormat()->setFormatCode('#,##0');

        return response()->streamDownload(function () use ($ss) {
            (new Xlsx($ss))->save('php://output');
        }, 'templat-daftar-penerima.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}