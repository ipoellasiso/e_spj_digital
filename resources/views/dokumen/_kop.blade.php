<table class="kop">
    <tr>
        <td class="logo">
@if ($data['logo'])
            <img src="{{ $data['logo'] }}" style="height:70px">
@endif
        </td>
        <td class="teks">
            <div class="pemda">{{ $data['s']['pemda'] }}</div>
            <div class="opd">{{ mb_strtoupper($data['s']['opd']['nama']) }}</div>
@if ($data['s']['opd']['alamat'])
            <div class="alamat">{{ $data['s']['opd']['alamat'] }}</div>
@endif
        </td>
        <td class="logo"></td>
    </tr>
</table>
<hr class="garis">