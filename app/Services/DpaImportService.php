<?php

namespace App\Services;

use App\Exceptions\DpaImportException;
use App\Imports\DpaImport;
use App\Models\AnggaranRekening;
use App\Models\DpaVersi;
use App\Models\ImportDpaBatch;
use App\Models\ImportDpaRow;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SubKegiatan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Alur: stage() -> (preview di UI) -> commit().
 * Data baru masuk tabel DPA hanya setelah commit() dan hanya bila staging bebas galat.
 */
class DpaImportService
{
    public function __construct(private DpaParser $parser)
    {
    }

    /**
     * @param  array{tahun:int, jenis?:string, nomor_dpa?:?string}  $meta
     */
    public function stage(Opd $opd, string|UploadedFile $berkas, string $namaFile, array $meta, ?int $userId = null): ImportDpaBatch
    {
        // Berkas unggahan diberikan sebagai UploadedFile agar tipe Excel dikenali dari ekstensi aslinya.
        $sheets = Excel::toArray(new DpaImport, $berkas);

        return $this->stageRows($opd, $sheets[0] ?? [], $namaFile, $meta, $userId);
    }

    /** Sama dengan stage(), tetapi menerima baris sheet yang sudah dibaca (dipakai juga oleh test). */
    public function stageRows(Opd $opd, array $rows, string $namaFile, array $meta, ?int $userId = null): ImportDpaBatch
    {
        $hasil = $this->parser->parse($rows);

        $catatan = [];
        if (! $hasil['unit_kode_list']) {
            $catatan[] = 'Kode unit OPD tidak ditemukan di file. Pastikan memakai laporan anggaran per program, kegiatan, dan sub kegiatan.';
        }
        foreach ($hasil['unit_kode_list'] as $kode) {
            if ($kode !== $opd->kode) {
                $catatan[] = "Kode unit di file ($kode) tidak sama dengan kode OPD Anda ({$opd->kode}).";
            }
        }
        if ($hasil['ringkasan']['sub_kegiatan'] === 0) {
            $catatan[] = 'Tidak ada sub kegiatan yang terbaca dari file.';
        }
        $jumlahError = count($hasil['errors']) + count($catatan);
        $r = $hasil['ringkasan'];

        return DB::transaction(function () use ($opd, $hasil, $catatan, $jumlahError, $r, $namaFile, $meta, $userId) {
            $batch = ImportDpaBatch::create([
                'opd_id' => $opd->id,
                'tahun' => $meta['tahun'],
                'jenis' => $meta['jenis'] ?? 'murni',
                'nomor_dpa' => $meta['nomor_dpa'] ?? null,
                'nama_file' => $namaFile,
                'unit_kode' => $hasil['unit_kode_list'][0] ?? null,
                'unit_nama' => $hasil['unit_nama'],
                'status' => $jumlahError === 0 ? 'siap' : 'gagal',
                'jumlah_program' => $r['program'],
                'jumlah_kegiatan' => $r['kegiatan'],
                'jumlah_sub_kegiatan' => $r['sub_kegiatan'],
                'jumlah_rekening' => $r['rekening'],
                'total_pagu' => DpaParser::formatSen($r['total_pagu_sen']),
                'jumlah_error' => $jumlahError,
                'catatan' => $catatan ?: null,
                'diunggah_oleh' => $userId,
            ]);

            foreach (array_chunk($hasil['rows'], 500) as $chunk) {
                ImportDpaRow::insert(array_map(fn (array $x) => [
                    'batch_id' => $batch->id,
                    'baris' => $x['baris'],
                    'level' => $x['level'],
                    'kode' => $x['kode'],
                    'induk_kode' => $x['induk_kode'],
                    'uraian' => mb_substr($x['uraian'], 0, 500),
                    'pagu' => $x['pagu_sen'] === null ? null : DpaParser::formatSen($x['pagu_sen']),
                    'pesan_error' => $x['error'] === null ? null : mb_substr($x['error'], 0, 255),
                ], $chunk));
            }

            return $batch;
        });
    }

    /**
     * Jadikan staging sebagai DPA versi baru (aktif) untuk OPD dan tahun tersebut.
     * Bila sudah ada versi aktif: realisasi_final dan reserved dibawa ke versi baru, dan commit
     * DITOLAK bila pagu baru lebih kecil dari realisasi + reserved, atau rekening yang sudah
     * punya realisasi hilang dari file.
     */
    public function commit(ImportDpaBatch $batch): DpaVersi
    {
        return DB::transaction(function () use ($batch) {
            $batch = ImportDpaBatch::withoutGlobalScopes()->lockForUpdate()->findOrFail($batch->id);
            if ($batch->status !== 'siap') {
                throw new DpaImportException('Batch tidak berstatus siap, tidak bisa dikomit.');
            }

            $opdId = $batch->opd_id;
            $lama = DpaVersi::withoutGlobalScopes()
                ->where('opd_id', $opdId)->where('tahun', $batch->tahun)->where('aktif', true)
                ->lockForUpdate()->first();
            $nomorVersi = 1 + (int) DpaVersi::withoutGlobalScopes()
                ->where('opd_id', $opdId)->where('tahun', $batch->tahun)->max('nomor_versi');

            $rows = ImportDpaRow::where('batch_id', $batch->id)->orderBy('baris')->get();
            $peta = $this->petaRekening($rows);
            $counter = $lama ? $this->counterVersiLama($lama) : [];

            // Nonaktifkan versi lama dulu agar unik (opd, tahun, aktif) tidak bentrok.
            $lama?->update(['aktif' => null]);

            $versi = DpaVersi::create([
                'opd_id' => $opdId,
                'tahun' => $batch->tahun,
                'jenis' => $batch->jenis,
                'nomor_versi' => $nomorVersi,
                'nomor_dpa' => $batch->nomor_dpa,
                'aktif' => true,
                'diimpor_oleh' => $batch->diunggah_oleh,
            ]);

            $idProgram = $idKegiatan = $idSub = [];
            $pelanggaran = [];
            $dasar = ['dpa_versi_id' => $versi->id, 'opd_id' => $opdId];

            foreach ($rows as $r) {
                switch ($r->level) {
                    case 'program':
                        $idProgram[$r->kode] = Program::create($dasar + ['kode' => $r->kode, 'uraian' => $r->uraian])->id;
                        break;
                    case 'kegiatan':
                        $idKegiatan[$r->kode] = Kegiatan::create($dasar + [
                            'program_id' => $idProgram[$r->induk_kode], 'kode' => $r->kode, 'uraian' => $r->uraian,
                        ])->id;
                        break;
                    case 'sub_kegiatan':
                        $idSub[$r->kode] = SubKegiatan::create($dasar + [
                            'kegiatan_id' => $idKegiatan[$r->induk_kode], 'kode' => $r->kode,
                            'uraian' => $r->uraian, 'pagu' => $r->pagu,
                        ])->id;
                        break;
                    case 'rekening':
                        $key = $r->induk_kode . '|' . $r->kode;
                        [$final, $reserved] = $counter[$key] ?? ['0.00', '0.00'];
                        unset($counter[$key]);
                        if ($this->sen($final) + $this->sen($reserved) > $this->sen($r->pagu)) {
                            $pelanggaran[] = "Sub {$r->induk_kode} rekening {$r->kode}: pagu baru {$r->pagu} lebih kecil dari realisasi + SPJ berproses ("
                                . DpaParser::formatSen($this->sen($final) + $this->sen($reserved)) . ').';
                        }
                        AnggaranRekening::create($dasar + [
                            'sub_kegiatan_id' => $idSub[$r->induk_kode],
                            'rekening_id' => $peta[$r->kode],
                            'pagu' => $r->pagu,
                            'realisasi_final' => $final,
                            'reserved' => $reserved,
                        ]);
                        break;
                }
            }

            foreach ($counter as $key => [$final, $reserved]) {
                if ($this->sen($final) + $this->sen($reserved) > 0) {
                    $pelanggaran[] = "Rekening $key tidak ada di file baru, padahal sudah punya realisasi atau SPJ berproses.";
                }
            }
            if ($pelanggaran) {
                throw new DpaImportException("Impor dibatalkan:\n- " . implode("\n- ", $pelanggaran)); // seluruh transaksi di-rollback
            }

            $batch->update(['status' => 'dikomit', 'dpa_versi_id' => $versi->id]);

            return $versi;
        });
    }

    /** kode rekening => id; rekening yang belum ada di master dibuat dari uraian di file. */
    private function petaRekening($rows): array
    {
        $unik = $rows->where('level', 'rekening')->unique('kode');
        $peta = [];
        foreach (array_chunk($unik->pluck('kode')->all(), 500) as $kodeChunk) {
            $peta += Rekening::whereIn('kode', $kodeChunk)->pluck('id', 'kode')->all();
        }
        foreach ($unik as $r) {
            if (! isset($peta[$r->kode])) {
                $peta[$r->kode] = Rekening::create(['kode' => $r->kode, 'nama' => mb_substr($r->uraian, 0, 500)])->id;
            }
        }

        return $peta;
    }

    /** "kodeSub|kodeRekening" => [realisasi_final, reserved] dari versi aktif sebelumnya. */
    private function counterVersiLama(DpaVersi $lama): array
    {
        return AnggaranRekening::withoutGlobalScopes()
            ->join('sub_kegiatan', 'sub_kegiatan.id', '=', 'anggaran_rekening.sub_kegiatan_id')
            ->join('rekening', 'rekening.id', '=', 'anggaran_rekening.rekening_id')
            ->where('anggaran_rekening.dpa_versi_id', $lama->id)
            ->get([
                'sub_kegiatan.kode as sub_kode', 'rekening.kode as rek_kode',
                'anggaran_rekening.realisasi_final as final', 'anggaran_rekening.reserved as reserved',
            ])
            ->mapWithKeys(fn ($x) => [$x->sub_kode . '|' . $x->rek_kode => [$x->final, $x->reserved]])
            ->all();
    }

    private function sen(string|int|float|null $v): int
    {
        return (int) round(((float) $v) * 100);
    }
}