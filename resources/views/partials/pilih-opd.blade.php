{{-- Hanya tampil untuk superadmin saat membuat data baru. Variabel: $daftarOpd --}}
@if ($daftarOpd->isNotEmpty())
    <div class="mb-3">
        <label for="opd_id" class="form-label fw-semibold">OPD</label>
        <select id="opd_id" name="opd_id" class="form-select @error('opd_id') is-invalid @enderror">
            <option value="">Pilih OPD</option>
            @foreach ($daftarOpd as $o)
                <option value="{{ $o->id }}" @selected((string) old('opd_id') === (string) $o->id)>{{ $o->nama }}</option>
            @endforeach
        </select>
        @error('opd_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
@endif