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
        
        // Jangan verifikasi jika sudah lunas
        if ($tagihan->status === 'paid') {
            return back()->with('error', 'Tagihan ini sudah lunas.');
        }

        // Update status tagihan
        $tagihan->update(['status' => 'paid']);

        // Perpanjang masa aktif OPD
        $opd = $tagihan->opd;
        
        // Logika: Jika tanggal berakhir masih di masa depan, tambahkan dari tanggal tersebut.
        // Jika sudah expired, hitung mulai dari hari ini.
        $tanggalMulai = $opd->tanggal_berakhir && $opd->tanggal_berakhir > now() 
                        ? Carbon::parse($opd->tanggal_berakhir) 
                        : now();
                        
        $opd->update([
            'tanggal_berakhir' => $tanggalMulai->addMonth(), // Tambah 1 bulan
            'paket_id' => $tagihan->paket_id
        ]);

        return back()->with('sukses', 'Pembayaran diverifikasi. Masa aktif OPD ' . $opd->nama . ' diperpanjang 1 bulan.');
    }
}