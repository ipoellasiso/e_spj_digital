<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Dokumen SPJ</title>
    @include('dokumen._gaya')
</head>
<body>
@foreach ($halaman as $h)
@if ($h['pisah'])
<div class="pisah"></div>
@endif
@include($h['view'], ['data' => $h['data']])
@endforeach
</body>
</html>