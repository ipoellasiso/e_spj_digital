@extends('layouts.app-tailwind')

@section('title', 'Beranda')
@section('nav', 'beranda')

{{-- Semua angka di bawah adalah contoh statis. Ganti dengan data dari controller. --}}
@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-hutan">Beranda</h1>
            <p class="text-redam">Posisi anggaran per {{ now()->translatedFormat('j F Y') }}</p>
        </div>
        <a href="#" class="btn-kuning"><i class="bi bi-plus-lg"></i> Buat SPJ</a>
    </div>

    {{-- Posisi pagu: satu batang yang membagi pagu menjadi final, dalam proses, dan sisa --}}
    <section class="panel mb-6 p-6" aria-labelledby="judul-posisi">
        <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
            <h2 id="judul-posisi" class="text-base font-bold text-hutan">Posisi pagu seluruh belanja</h2>
            <span class="angka text-redam">Pagu <strong class="text-tinta">Rp2.450.000.000</strong></span>
        </div>

        <div class="flex h-[38px] overflow-hidden rounded-lg border border-garis bg-[#eef2ec]"
             role="img" aria-label="Realisasi final 56 persen, SPJ dalam proses 7,6 persen, sisa pagu 36,4 persen">
            <div class="bg-daun" style="width:56%"></div>
            <div class="bg-kuning" style="width:7.6%"></div>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-3">
            <div>
                <div class="flex items-center gap-2 text-sm text-redam"><span class="h-3 w-3 rounded-sm bg-daun"></span> Realisasi final</div>
                <div class="angka text-xl font-bold">Rp1.372.500.000</div>
                <div class="text-sm text-redam">56,0% dari pagu</div>
            </div>
            <div>
                <div class="flex items-center gap-2 text-sm text-redam"><span class="h-3 w-3 rounded-sm bg-kuning"></span> SPJ dalam proses</div>
                <div class="angka text-xl font-bold">Rp186.300.000</div>
                <div class="text-sm text-redam">7,6% dari pagu, sudah mengurangi sisa</div>
            </div>
            <div>
                <div class="flex items-center gap-2 text-sm text-redam"><span class="h-3 w-3 rounded-sm border border-garis bg-[#eef2ec]"></span> Sisa pagu</div>
                <div class="angka text-xl font-bold">Rp891.200.000</div>
                <div class="text-sm text-redam">36,4% dari pagu</div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-12">
        {{-- SPJ terbaru --}}
        <section class="panel xl:col-span-7">
            <div class="panel-kepala">
                <h2>SPJ terbaru</h2>
                <a href="#" class="text-sm text-daun-tua underline-offset-2 hover:underline">Lihat semua SPJ</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                    <tr><th class="th">Nomor</th><th class="th">Uraian</th><th class="th text-right">Jumlah</th><th class="th">Status</th></tr>
                    </thead>
                    <tbody class="angka">
                    <tr><td class="td font-semibold">0263</td><td class="td">Service kendaraan pick up<div class="text-sm text-redam">5.1.02.01.001.00020</div></td><td class="td text-right">Rp1.578.309</td><td class="td"><span class="st st-final">Final</span></td></tr>
                    <tr><td class="td font-semibold">0264</td><td class="td">Belanja alat tulis kantor</td><td class="td text-right">Rp2.340.000</td><td class="td"><span class="st st-diajukan">Diajukan</span></td></tr>
                    <tr><td class="td font-semibold">0265</td><td class="td">Makan minum rapat koordinasi</td><td class="td text-right">Rp4.750.000</td><td class="td"><span class="st st-verif">Diverifikasi bendahara</span></td></tr>
                    <tr><td class="td font-semibold">0266</td><td class="td">Belanja bahan bakar<div class="text-sm text-bata">Dikembalikan: nota belum dilampirkan</div></td><td class="td text-right">Rp1.200.000</td><td class="td"><span class="st st-kembali">Dikembalikan</span></td></tr>
                    <tr><td class="td font-semibold text-redam">-</td><td class="td">Belanja cetak spanduk</td><td class="td text-right">Rp685.000</td><td class="td"><span class="st st-draft">Draft</span></td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Antrian tindakan --}}
        <section class="panel xl:col-span-5">
            <div class="panel-kepala">
                <h2>Menunggu verifikasi Anda</h2>
                <span class="st st-diajukan">3 SPJ</span>
            </div>
            <ul>
                @foreach ([['0264', 'Belanja alat tulis kantor', 'Rp2.340.000', 'Diajukan 20 Nov'], ['0267', 'Jasa kebersihan kantor', 'Rp3.000.000', 'Diajukan 20 Nov'], ['0268', 'Perawatan AC ruang rapat', 'Rp1.850.000', 'Diajukan 19 Nov']] as [$no, $uraian, $jumlah, $kapan])
                    <li class="flex items-center gap-3 border-b border-garis px-6 py-3">
                        <div class="flex-1">
                            <div class="font-semibold">{{ $uraian }}</div>
                            <div class="angka text-sm text-redam">No. {{ $no }}, {{ $jumlah }}, {{ $kapan }}</div>
                        </div>
                        <a href="#" class="btn-garis">Verifikasi</a>
                    </li>
                @endforeach
            </ul>

            <div class="px-6 pb-6 pt-4">
                <div class="mb-2 font-semibold text-hutan">Sisa pagu paling tipis</div>
                @foreach ([['Belanja bahan bakar dan pelumas', 8], ['Belanja alat tulis kantor', 14], ['Belanja jasa kebersihan', 22]] as [$nama, $sisa])
                    <div class="mb-2">
                        <div class="flex justify-between text-sm"><span>{{ $nama }}</span><span class="angka text-redam">sisa {{ $sisa }}%</span></div>
                        <div class="h-2 overflow-hidden rounded-full bg-[#eef2ec]">
                            <div class="h-full {{ $sisa <= 10 ? 'bg-bata' : 'bg-daun' }}" style="width:{{ 100 - $sisa }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection