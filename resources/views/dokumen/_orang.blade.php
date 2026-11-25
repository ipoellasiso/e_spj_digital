@if ($p['nama'] !== '')
<div class="nama">{{ $p['nama'] }}</div>
@if ($p['nip'] !== '')
<div>NIP. {{ $p['nip'] }}</div>
@endif
@else
<div>..............................</div>
@endif