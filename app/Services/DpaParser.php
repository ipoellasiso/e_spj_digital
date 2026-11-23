<?php

namespace App\Services;

/**
 * Parser murni (tanpa DB dan tanpa framework) untuk file Excel laporan anggaran.
 * Hanya membaca kolom B (format "kode - uraian") dan kolom C (pagu); kolom lain diabaikan.
 * Level baris ditentukan dari pola kode, bukan dari posisi baris.
 *
 * Nilai uang dihitung dalam "sen" (integer) supaya bebas galat pembulatan float.
 */
class DpaParser
{
    private const RE_PROGRAM  = '/^\d\.\d{2}\.\d{2}$/';                         // 1.05.01
    private const RE_KEGIATAN = '/^\d\.\d{2}\.\d{2}\.\d\.\d{2}$/';              // 1.05.01.2.01
    private const RE_SUB      = '/^\d\.\d{2}\.\d{2}\.\d\.\d{2}\.\d{4}$/';       // 1.05.01.2.01.0001
    private const RE_UNIT     = '/^\d\.\d{2}\.\d\.\d{2}\.\d\.\d{2}\.\d{2}\.\d{4}$/'; // 1.05.0.00.0.00.01.0000
    private const RE_REKENING = '/^5(\.\d+)+$/';                                // 5.1.02.01.001.00026

    /** Selisih pagu sub kegiatan vs jumlah rekening yang masih ditoleransi: Rp1. */
    private const TOLERANSI_SEN = 100;

    /**
     * @param  array<int, array<int, mixed>>  $rows  baris sheet (kolom A=0, B=1, C=2, ...)
     * @return array{unit_kode_list: string[], unit_nama: ?string, rows: array, errors: array, ringkasan: array}
     */
    public function parse(array $rows): array
    {
        $hasil = [
            'unit_kode_list' => [],
            'unit_nama' => null,
            'rows' => [],
            'errors' => [],
            'ringkasan' => ['program' => 0, 'kegiatan' => 0, 'sub_kegiatan' => 0, 'rekening' => 0, 'total_pagu_sen' => 0],
        ];

        $program = $kegiatan = $sub = null;
        $subIdx = [];        // kode sub => indeks di $hasil['rows']
        $subJumlahSen = [];  // kode sub => jumlah pagu rekening (sen)
        $subJumlahRek = [];  // kode sub => banyak rekening valid
        $subAdaGalat = [];   // kode sub => true bila ada rekening bermasalah (lewati cek selisih)
        $seen = [];

        foreach (array_values($rows) as $i => $row) {
            $baris = $i + 1;
            $teks = $this->bersihkan($row[1] ?? null);
            if ($teks === null || ! preg_match('/^(\d[\d.]*)\s+-\s+(.+)$/u', $teks, $m)) {
                continue; // judul, header, kosong, JUMLAH, PERSENTASE, dll.
            }
            $kode = $m[1];
            $uraian = trim($m[2]);
            $paguSen = self::keSen($row[2] ?? null);

            if (preg_match(self::RE_PROGRAM, $kode)) {
                $program = $kode;
                $kegiatan = $sub = null;
                $this->catat($hasil, 'program', $kode, null, $uraian, null, null, $baris);
                $hasil['ringkasan']['program']++;
            } elseif (preg_match(self::RE_KEGIATAN, $kode)) {
                $err = ($program === null || ! str_starts_with($kode, $program . '.'))
                    ? "Kegiatan $kode tidak berada di bawah program yang sedang dibaca." : null;
                $kegiatan = $kode;
                $sub = null;
                $this->catat($hasil, 'kegiatan', $kode, $program, $uraian, null, $err, $baris);
                $hasil['ringkasan']['kegiatan']++;
            } elseif (preg_match(self::RE_SUB, $kode)) {
                $err = null;
                if ($kegiatan === null || ! str_starts_with($kode, $kegiatan . '.')) {
                    $err = "Sub kegiatan $kode tidak berada di bawah kegiatan yang sedang dibaca.";
                } elseif ($paguSen === null || $paguSen < 0) {
                    // Pagu 0 diterima: DPA asli memuat sub kegiatan berpagu 0 (tanpa belanja).
                    $err = 'Pagu sub kegiatan kosong, bukan angka, atau negatif.';
                }
                if (isset($subIdx[$kode])) {
                    $err = 'Sub kegiatan ganda di file.';
                }
                $idx = $this->catat($hasil, 'sub_kegiatan', $kode, $kegiatan, $uraian, $paguSen, $err, $baris);
                if (! isset($subIdx[$kode])) {
                    $subIdx[$kode] = $idx;
                    $subJumlahSen[$kode] = 0;
                    $subJumlahRek[$kode] = 0;
                    $hasil['ringkasan']['sub_kegiatan']++;
                    if ($err === null) {
                        $hasil['ringkasan']['total_pagu_sen'] += $paguSen;
                    }
                }
                $sub = $kode;
            } elseif (preg_match(self::RE_UNIT, $kode)) {
                if (! in_array($kode, $hasil['unit_kode_list'], true)) {
                    $hasil['unit_kode_list'][] = $kode;
                }
                $hasil['unit_nama'] ??= $uraian;
            } elseif (preg_match(self::RE_REKENING, $kode)) {
                $err = null;
                if ($sub === null) {
                    $err = 'Rekening belanja muncul sebelum sub kegiatan.';
                } elseif ($paguSen === null || $paguSen < 0) {
                    // Pagu 0 diterima: file asli memuat rekening berpagu 0 (tidak bisa dipakai SPJ, sisa pagu 0).
                    $err = 'Pagu rekening kosong, bukan angka, atau negatif.';
                } elseif (isset($seen[$sub . '|' . $kode])) {
                    $err = 'Rekening ganda pada sub kegiatan yang sama.';
                }
                $this->catat($hasil, 'rekening', $kode, $sub, $uraian, $paguSen, $err, $baris);
                $hasil['ringkasan']['rekening']++;
                if ($sub !== null) {
                    $seen[$sub . '|' . $kode] = true;
                    if ($err === null) {
                        $subJumlahSen[$sub] += $paguSen;
                        $subJumlahRek[$sub]++;
                    } else {
                        $subAdaGalat[$sub] = true;
                    }
                }
            } else {
                $this->catat($hasil, 'tidak_dikenal', mb_substr($kode, 0, 40), null, $uraian, null, "Format kode tidak dikenali: $kode", $baris);
            }
        }

        // Cek silang: pagu sub kegiatan harus sama dengan jumlah pagu rekening di bawahnya.
        foreach ($subIdx as $kode => $idx) {
            $paguSub = $hasil['rows'][$idx]['pagu_sen'];
            if (! empty($subAdaGalat[$kode]) || $paguSub === null) {
                continue;
            }
            if ($subJumlahRek[$kode] === 0) {
                // Sub kegiatan berpagu 0 boleh tanpa rekening; yang berpagu tapi tanpa rekening tidak wajar.
                if ($paguSub > 0) {
                    $this->tandai($hasil, $idx, 'Sub kegiatan tidak punya rekening belanja.');
                }
            } elseif (abs($paguSub - $subJumlahSen[$kode]) > self::TOLERANSI_SEN) {
                $this->tandai($hasil, $idx, sprintf(
                    'Pagu sub kegiatan (%s) tidak sama dengan jumlah rekening (%s).',
                    $this->rupiah($paguSub),
                    $this->rupiah($subJumlahSen[$kode])
                ));
            }
        }

        return $hasil;
    }

    /** Ubah nilai sel menjadi sen. Null bila kosong atau bukan angka. Mengerti "1.810.500,00". */
    public static function keSen(mixed $v): ?int
    {
        if ($v === null) {
            return null;
        }
        if (is_int($v) || is_float($v)) {
            return (int) round($v * 100);
        }
        if (! is_string($v)) {
            return null;
        }
        $s = preg_replace('/[Rr][Pp]\.?|\s|\x{00A0}/u', '', $v);
        if ($s === null || $s === '') {
            return null;
        }
        if (str_contains($s, ',') && str_contains($s, '.')) {
            // titik terakhir vs koma terakhir menentukan mana pemisah desimal
            $s = strrpos($s, ',') > strrpos($s, '.')
                ? str_replace(',', '.', str_replace('.', '', $s))   // 1.810.500,00
                : str_replace(',', '', $s);                          // 1,810,500.00
        } elseif (substr_count($s, ',') > 1) {
            $s = str_replace(',', '', $s);                           // 1,500,000
        } elseif (str_contains($s, ',')) {
            $s = str_replace(',', '.', $s);                          // 1500,50
        } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $s)) {
            $s = str_replace('.', '', $s);                           // 1.500.000
        }

        return is_numeric($s) ? (int) round(((float) $s) * 100) : null;
    }

    /** Sen -> string desimal tepat, mis. 181050000 -> "1810500.00" (tanpa float). */
    public static function formatSen(int $sen): string
    {
        $abs = abs($sen);

        return ($sen < 0 ? '-' : '') . intdiv($abs, 100) . '.' . str_pad((string) ($abs % 100), 2, '0', STR_PAD_LEFT);
    }

    private function bersihkan(mixed $v): ?string
    {
        if (! is_string($v) && ! is_numeric($v)) {
            return null;
        }
        $s = trim((string) preg_replace('/[\s\x{00A0}]+/u', ' ', (string) $v));

        return $s === '' ? null : $s;
    }

    private function rupiah(int $sen): string
    {
        return 'Rp' . number_format($sen / 100, 2, ',', '.');
    }

    private function catat(array &$hasil, string $level, string $kode, ?string $induk, string $uraian, ?int $paguSen, ?string $err, int $baris): int
    {
        $hasil['rows'][] = [
            'baris' => $baris, 'level' => $level, 'kode' => $kode, 'induk_kode' => $induk,
            'uraian' => $uraian, 'pagu_sen' => $paguSen, 'error' => $err,
        ];
        if ($err !== null) {
            $hasil['errors'][] = ['baris' => $baris, 'pesan' => $err];
        }

        return array_key_last($hasil['rows']);
    }

    private function tandai(array &$hasil, int $idx, string $pesan): void
    {
        $r = &$hasil['rows'][$idx];
        $r['error'] = $r['error'] === null ? $pesan : $r['error'] . ' | ' . $pesan;
        $hasil['errors'][] = ['baris' => $r['baris'], 'pesan' => $pesan];
    }
}