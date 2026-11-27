<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tagihan;
use App\Models\Opd;
use Carbon\Carbon;

class SuperadminTagihanController extends Controller
{
    // 1. Menampilkan daftar tagihan yang masuk
    public function index()
    {
        // Ambil semua tagihan, urutkan dari yang terbaru
        $tagihans = Tagihan::with(['opd', 'paket'])
            ->orderBy('status', 'asc') // unpaid dulu
            ->latest()
            ->get();

        return view('superadmin.tagihan', compact('tagihans'));
    }

    // 2. Fungsi yang kamu tanyakan tadi (Verifikasi)
    public function verifikasiPembayaran($tagihanId)
    {
        $tagihan = Tagihan::findOrFail($tagihanId);
        
        if ($tagihan->status === 'paid') {
            return back()->with('error', 'Tagihan ini sudah lunas.');
        }

        // 1. Update status tagihan
        $tagihan->update(['status' => 'paid']);

        // 2. Perpanjang masa aktif OPD di tabel 'opd'
        $opd = $tagihan->opd;
        
        // Cek apakah ini tagihan tahunan atau bulanan berdasarkan selisih hari
        // (Karena di database kita tidak menyimpan kolom 'durasi', kita hitung dari periode)
        $mulai = \Carbon\Carbon::parse($tagihan->periode_mulai);
        $berakhir = \Carbon\Carbon::parse($tagihan->periode_berakhir);
        $durasiHari = $mulai->diffInDays($berakhir); 

        // Tentukan tanggal mulai (jika masih aktif, lanjut dari tanggal berakhir. Jika expired, mulai dari hari ini)
        $tanggalMulai = $opd->tanggal_berakhir && $opd->tanggal_berakhir > now() 
                        ? \Carbon\Carbon::parse($opd->tanggal_berakhir) 
                        : now();

        // 3. Tambahkan masa aktif OPD sesuai durasi tagihan
        if ($durasiHari > 300) {
            // Jika lebih dari 300 hari, anggap Tahunan
            $opd->update([
                'tanggal_berakhir' => $tanggalMulai->addYear(),
                'paket_id' => $tagihan->paket_id
            ]);
        } else {
            // Jika kurang dari 300 hari, anggap Bulanan
            $opd->update([
                'tanggal_berakhir' => $tanggalMulai->addMonth(),
                'paket_id' => $tagihan->paket_id
            ]);
        }

        return back()->with('sukses', 'Pembayaran diverifikasi. Masa aktif OPD ' . $opd->nama . ' telah diperpanjang.');
    }
}