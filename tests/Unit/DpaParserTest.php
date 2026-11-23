<?php

namespace Tests\Unit;

use App\Services\DpaParser;
use PHPUnit\Framework\TestCase;
use Tests\Support\DpaFixture;

class DpaParserTest extends TestCase
{
    private function pesan(array $hasil): string
    {
        return implode(' | ', array_column($hasil['errors'], 'pesan'));
    }

    public function test_membaca_hierarki_dan_ringkasan(): void
    {
        $hasil = (new DpaParser)->parse(DpaFixture::rows());

        $this->assertSame([], $hasil['errors']);
        $this->assertSame(DpaFixture::UNIT_KODE, $hasil['unit_kode_list'][0]);
        $this->assertSame('Dinas Pemadam Kebakaran dan Penyelamatan', $hasil['unit_nama']);
        $this->assertSame(1, $hasil['ringkasan']['program']);
        $this->assertSame(1, $hasil['ringkasan']['kegiatan']);
        $this->assertSame(2, $hasil['ringkasan']['sub_kegiatan']);
        $this->assertSame(4, $hasil['ringkasan']['rekening']);
        $this->assertSame(383550000, $hasil['ringkasan']['total_pagu_sen']); // Rp3.835.500
    }

    public function test_baris_judul_header_jumlah_dan_persentase_diabaikan(): void
    {
        $hasil = (new DpaParser)->parse(DpaFixture::rows());

        // 1 program + 1 kegiatan + 2 sub kegiatan + 4 rekening; baris unit tidak disimpan
        $this->assertCount(8, $hasil['rows']);
        $levels = array_unique(array_column($hasil['rows'], 'level'));
        sort($levels);
        $this->assertSame(['kegiatan', 'program', 'rekening', 'sub_kegiatan'], $levels);
    }

    public function test_induk_kode_menunjuk_level_di_atasnya(): void
    {
        $rows = (new DpaParser)->parse(DpaFixture::rows())['rows'];
        $byKode = [];
        foreach ($rows as $r) {
            $byKode[$r['level'] . ':' . $r['kode']] = $r;
        }

        $this->assertSame('1.05.01', $byKode['kegiatan:1.05.01.2.01']['induk_kode']);
        $this->assertSame('1.05.01.2.01', $byKode['sub_kegiatan:1.05.01.2.01.0001']['induk_kode']);
        $this->assertSame('1.05.01.2.01.0001', $rows[4]['induk_kode']); // rekening pertama
    }

    public function test_pagu_sub_kegiatan_harus_sama_dengan_jumlah_rekening(): void
    {
        $hasil = (new DpaParser)->parse(DpaFixture::rows(310500, 1900000));

        $this->assertCount(1, $hasil['errors']);
        $this->assertStringContainsString('tidak sama dengan jumlah rekening', $this->pesan($hasil));
    }

    public function test_selisih_dalam_toleransi_rp1_tidak_dianggap_galat(): void
    {
        $hasil = (new DpaParser)->parse(DpaFixture::rows(310500, 1810500 + 1));

        $this->assertSame([], $hasil['errors']);
    }

    public function test_rekening_ganda_pada_sub_kegiatan_yang_sama(): void
    {
        $rows = DpaFixture::rows();
        array_splice($rows, 10, 0, [[null, '5.1.02.02.001.00071 - Belanja Lembur', 1500000]]);

        $hasil = (new DpaParser)->parse($rows);

        $this->assertStringContainsString('Rekening ganda', $this->pesan($hasil));
    }

    public function test_rekening_sebelum_sub_kegiatan(): void
    {
        $rows = [[null, '5.1.02.02.001.00071 - Belanja Lembur', 1500000]];

        $hasil = (new DpaParser)->parse($rows);

        $this->assertStringContainsString('sebelum sub kegiatan', $this->pesan($hasil));
    }

    public function test_kode_tidak_dikenali(): void
    {
        $hasil = (new DpaParser)->parse([[null, '9.99.9 - Kode aneh', 1000]]);

        $this->assertStringContainsString('Format kode tidak dikenali', $this->pesan($hasil));
    }

    public function test_sub_kegiatan_tanpa_pagu_atau_tanpa_rekening(): void
    {
        $rows = [
            [null, '1.05.01 - PROGRAM X'],
            [null, '1.05.01.2.01 - Kegiatan X'],
            [1, '1.05.01.2.01.0001 - Sub tanpa pagu', null],
            [2, '1.05.01.2.01.0002 - Sub tanpa rekening', 5000],
        ];

        $pesan = $this->pesan((new DpaParser)->parse($rows));

        $this->assertStringContainsString('Pagu sub kegiatan kosong', $pesan);
        $this->assertStringContainsString('tidak punya rekening belanja', $pesan);
    }

    public function test_rekening_berpagu_nol_diterima_tetapi_negatif_atau_kosong_ditolak(): void
    {
        $dasar = [
            [null, '1.05.01 - PROGRAM X'],
            [null, '1.05.01.2.01 - Kegiatan X'],
            [1, '1.05.01.2.01.0001 - Sub X', 1000],
        ];

        $nol = (new DpaParser)->parse([...$dasar, [null, '5.1.02.02.001.00026 - Jasa A', 0], [null, '5.1.02.02.001.00027 - Jasa B', 1000]]);
        $this->assertSame([], $nol['errors']);
        $this->assertSame(2, $nol['ringkasan']['rekening']);

        $negatif = (new DpaParser)->parse([...$dasar, [null, '5.1.02.02.001.00026 - Jasa A', -5]]);
        $this->assertStringContainsString('negatif', $this->pesan($negatif));

        $kosong = (new DpaParser)->parse([...$dasar, [null, '5.1.02.02.001.00026 - Jasa A', null]]);
        $this->assertStringContainsString('kosong', $this->pesan($kosong));
    }

    public function test_ke_sen_mengerti_angka_dan_format_indonesia(): void
    {
        $this->assertSame(181050000, DpaParser::keSen(1810500));
        $this->assertSame(1065140183178, DpaParser::keSen(10651401831.779999)); // float dari Excel
        $this->assertSame(181050000, DpaParser::keSen('1.810.500,00'));
        $this->assertSame(181050000, DpaParser::keSen('Rp 1.810.500'));
        $this->assertSame(181050000, DpaParser::keSen('1,810,500.00'));
        $this->assertSame(150050, DpaParser::keSen('1500,50'));
        $this->assertNull(DpaParser::keSen(null));
        $this->assertNull(DpaParser::keSen(''));
        $this->assertNull(DpaParser::keSen('abc'));
    }

    public function test_format_sen_tepat_tanpa_float(): void
    {
        $this->assertSame('1810500.00', DpaParser::formatSen(181050000));
        $this->assertSame('0.05', DpaParser::formatSen(5));
        $this->assertSame('14643604935.78', DpaParser::formatSen(1464360493578));
    }
}