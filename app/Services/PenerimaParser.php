<?php

namespace App\Services;

use App\Support\Uang;

/**
 * Membaca daftar penerima honor dari baris sheet Excel (parser murni, tanpa DB).
 * Kolom dikenali dari judulnya (urutan bebas): Nama, Jabatan, Honor, Nama Bank, No. Rek, Pajak.
 * Tanpa baris judul, urutan kolom dianggap: Nama, Jabatan, Honor, Bank, No. Rek, Pajak.
 */
class PenerimaParser
{
    public const MAKSIMAL = 500;

    private const JUDUL = [
        'nama' => ['nama', 'namapenerima', 'penerima'],
        'jabatan' => ['jabatan', 'keterangan'],
        'honor' => ['honor', 'honorrp', 'honorinsentif', 'insentif', 'jumlah', 'bruto', 'nilai'],
        'bank' => ['namabank', 'bank'],
        'rekening' => ['norek', 'norekening', 'nomorrekening', 'rekening', 'nomorrek'],
        'pajak' => ['pajak', 'nilaipajak', 'nilaipajakrp', 'pajakrp', 'pph', 'pph21'],
    ];

    private const URUTAN_BAKU = ['nama', 'jabatan', 'honor', 'bank', 'rekening', 'pajak'];

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @return array{penerima: array<int, array<string, string>>, galat: string[]}
     */
    public function parse(array $rows): array
    {
        $rows = array_values($rows);
        [$peta, $mulai] = $this->petaKolom($rows);

        $penerima = [];
        $galat = [];
        for ($i = $mulai; $i < count($rows); $i++) {
            $baris = $rows[$i];
            $nama = $this->teks($this->sel($baris, $peta, 'nama'));
            $honorMentah = $this->sel($baris, $peta, 'honor');
            if ($nama === '' && $this->teks($honorMentah) === '') {
                continue; // baris kosong
            }
            if (str_starts_with(mb_strtolower($nama), 'total')) {
                continue; // baris jumlah
            }

            $no = $i + 1;
            $honor = Uang::dariInput(is_string($honorMentah) ? trim($honorMentah) : $honorMentah);
            if ($nama === '') {
                $galat[] = "Baris {$no}: nama penerima kosong.";
                continue;
            }
            if ($honor === null || $honor <= 0) {
                $galat[] = "Baris {$no} ({$nama}): honor kosong atau bukan angka lebih dari 0.";
                continue;
            }

            $pajakMentah = $this->sel($baris, $peta, 'pajak');
            $pajak = $this->teks($pajakMentah) === '' ? null : Uang::dariInput(is_string($pajakMentah) ? trim($pajakMentah) : $pajakMentah);
            if ($this->teks($pajakMentah) !== '' && ($pajak === null || $pajak < 0)) {
                $galat[] = "Baris {$no} ({$nama}): pajak bukan angka.";
                continue;
            }
            if ($pajak !== null && $pajak > $honor) {
                $galat[] = "Baris {$no} ({$nama}): pajak lebih besar dari honor.";
                continue;
            }

            $penerima[] = [
                'uraian' => $nama,
                'jabatan' => $this->teks($this->sel($baris, $peta, 'jabatan')),
                'harga_satuan' => Uang::isianHarga(Uang::format($honor)),
                'nama_bank' => $this->teks($this->sel($baris, $peta, 'bank')),
                'no_rekening' => $this->teks($this->sel($baris, $peta, 'rekening')),
                'pajak' => $pajak === null ? '' : Uang::isianHarga(Uang::format($pajak)),
            ];
            if (count($penerima) > self::MAKSIMAL) {
                $galat[] = 'Maksimal ' . self::MAKSIMAL . ' penerima per berkas.';
                break;
            }
        }

        if (! $penerima && ! $galat) {
            $galat[] = 'Tidak ada baris penerima yang terbaca. Pastikan memakai templat dan kolom Nama serta Honor terisi.';
        }

        return ['penerima' => $penerima, 'galat' => $galat];
    }

    /** @return array{0: array<string, int>, 1: int} peta kolom (nama => indeks) dan indeks baris data pertama */
    private function petaKolom(array $rows): array
    {
        foreach (array_slice($rows, 0, 10, true) as $i => $baris) {
            $peta = [];
            foreach ((array) $baris as $kolom => $sel) {
                $kunci = $this->normal($sel);
                foreach (self::JUDUL as $nama => $alias) {
                    if (! isset($peta[$nama]) && $kunci !== '' && in_array($kunci, $alias, true)) {
                        $peta[$nama] = (int) $kolom;
                    }
                }
            }
            if (isset($peta['nama'], $peta['honor'])) {
                return [$peta, $i + 1];
            }
        }

        // Tanpa judul: urutan baku. Lewati baris pertama bila kolom honornya bukan angka (kemungkinan judul).
        $peta = array_flip(self::URUTAN_BAKU);
        $mulai = 0;
        if ($rows && Uang::dariInput(is_string($rows[0][2] ?? null) ? trim($rows[0][2]) : ($rows[0][2] ?? null)) === null) {
            $mulai = 1;
        }

        return [$peta, $mulai];
    }

    private function sel(array $baris, array $peta, string $nama): mixed
    {
        return isset($peta[$nama]) ? ($baris[$peta[$nama]] ?? null) : null;
    }

    /** Sel ke teks. Angka besar (mis. nomor rekening) tidak boleh berubah menjadi notasi ilmiah. */
    private function teks(mixed $v): string
    {
        if ($v === null) {
            return '';
        }
        if (is_float($v) && floor($v) === $v) {
            return number_format($v, 0, '', '');
        }

        return trim((string) $v);
    }

    private function normal(mixed $v): string
    {
        return preg_replace('/[^a-z0-9]/', '', mb_strtolower((string) $v)) ?? '';
    }
}