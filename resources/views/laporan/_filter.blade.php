{{-- Variabel: $aksi (nama rute), $daftarOpd, $opd, $tahun, $sampai, $dari (hanya mode 'rentang'), $mode ('rentang' | 'sampai') --}}
<form method="GET" action="{{ route($aksi) }}" class="panel p-3 mb-4 d-flex flex-wrap align-items-end gap-3">
    @if ($daftarOpd->isNotEmpty())
        <div>
            <label for="opd" class="form-label small fw-semibold mb-1">OPD</label>
            <select id="opd" name="opd" class="form-select">
                <option value="">Pilih OPD</option>
                @foreach ($daftarOpd as $o)
                    <option value="{{ $o->id }}" @selected($opd && $opd->id === $o->id)>{{ $o->nama }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <div>
        <label for="tahun" class="form-label small fw-semibold mb-1">Tahun anggaran</label>
        <input id="tahun" name="tahun" type="number" min="2000" max="2100" value="{{ $tahun }}" class="form-control" style="width:120px">
    </div>
    @if ($mode === 'rentang')
        <div>
            <label for="dari" class="form-label small fw-semibold mb-1">Dari tanggal</label>
            <input id="dari" name="dari" type="date" value="{{ $dari->format('Y-m-d') }}" class="form-control">
        </div>
    @endif
    <div>
        <label for="sampai" class="form-label small fw-semibold mb-1">{{ $mode === 'rentang' ? 'Sampai tanggal' : 'Realisasi sampai tanggal' }}</label>
        <input id="sampai" name="sampai" type="date" value="{{ $sampai->format('Y-m-d') }}" class="form-control">
    </div>
    <button type="submit" class="btn btn-outline-daun">Tampilkan</button>
</form>