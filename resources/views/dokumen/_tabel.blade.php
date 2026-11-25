<table class="barang">
    <tr>
        <th style="width:32px">No</th>
        <th>Nama Barang</th>
        <th style="width:55px">Volume</th>
        <th style="width:60px">Satuan</th>
        <th style="width:85px">Harga</th>
        <th style="width:90px">Jumlah</th>
    </tr>
@foreach ($data['items'] as $b)
    <tr>
        <td class="tengah">{{ $b['no'] }}</td>
        <td>{{ $b['uraian'] }}</td>
        <td class="tengah">{{ $b['volume'] }}</td>
        <td class="tengah">{{ $b['satuan'] }}</td>
        <td class="kanan">{{ $b['harga'] }}</td>
        <td class="kanan">{{ $b['jumlah'] }}</td>
    </tr>
@endforeach
    <tr>
        <td colspan="4"><b>Terbilang :</b> <i>{{ $data['terbilang'] }}</i></td>
        <td class="tengah"><b>Total</b></td>
        <td class="kanan"><b>{{ $data['total'] }}</b></td>
    </tr>
</table>