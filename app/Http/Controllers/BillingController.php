<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tagihan;
use App\Models\Paket;
use Carbon\Carbon;

class BillingController extends Controller
{
    public function index()
    {
        $opd = auth()->user()->opd;
        
        // Ambil tagihan yang belum dibayar
        $tagihanAktif = Tagihan::where('opd_id', $opd->id)
            ->where('status', 'unpaid')
            ->latest()->first();

        // Ambil riwayat tagihan
        $riwayat = Tagihan::where('opd_id', $opd->id)
            ->whereIn('status', ['paid', 'expired'])
            ->latest()->get();

        // Cek sisa hari langganan
        $sisaHari = null;
        $isExpired = false;
        if ($opd->tanggal_berakhir) {
            $sisaHari = now()->diffInDays(Carbon::parse($opd->tanggal_berakhir), false);
            $isExpired = $sisaHari < 0 || $sisaHari === 0;
        } else {
            $isExpired = true; // Belum pernah langganan
        }

        // Ambil daftar paket untuk perpanjangan
        $daftarPaket = Paket::all();

        return view('billing.index', compact('opd', 'tagihanAktif', 'riwayat', 'sisaHari', 'isExpired', 'daftarPaket'));
    }

    public function perpanjang(Request $request)
    {
        $request->validate([
            'paket_id' => 'required|exists:pakets,id',
            'durasi' => 'required|in:bulanan,tahunan', // Pastikan validasi ini ada
        ]);
        
        $opd = auth()->user()->opd;
        $paket = Paket::findOrFail($request->paket_id);

        // Cek apakah sudah ada tagihan unpaid
        $cekTertunda = Tagihan::where('opd_id', $opd->id)->where('status', 'unpaid')->exists();
        if ($cekTertunda) {
            return back()->with('error', 'Anda masih memiliki tagihan yang belum dibayar.');
        }

        // Tentukan total bayar dan masa aktif berdasarkan input durasi
        if ($request->durasi === 'tahunan') {
            $totalBayar = $paket->harga_tahunan;
            $masaAktif = now()->addYear(); // Tambah 1 Tahun
        } else {
            $totalBayar = $paket->harga_bulanan;
            $masaAktif = now()->addMonth(); // Tambah 1 Bulan
        }

        Tagihan::create([
            'opd_id' => $opd->id,
            'paket_id' => $paket->id,
            'no_invoice' => 'INV-' . date('Ymd') . '-' . rand(100, 999),
            'total_bayar' => $totalBayar,
            'periode_mulai' => now(),
            'periode_berakhir' => $masaAktif, // <-- Ini yang akan menyimpan 1 tahun atau 1 bulan
            'status' => 'unpaid'
        ]);

        return back()->with('sukses', 'Tagihan perpanjangan berhasil dibuat. Silakan lakukan pembayaran.');
    }

    public function uploadBukti(Request $request, $id)
    {
        $request->validate(['bukti' => 'required|image|max:2048']);
        $tagihan = Tagihan::findOrFail($id);
        
        $path = $request->file('bukti')->store('bukti-transfer', 'public');
        $tagihan->update(['bukti_transfer' => $path]);

        return back()->with('sukses', 'Bukti transfer berhasil diunggah. Menunggu verifikasi admin.');
    }
    
    public function cetakInvoice(Tagihan $tagihan)
    {
        // Pastikan OPD hanya bisa mencetak tagihannya sendiri
        if ($tagihan->opd_id !== auth()->user()->opd_id && !auth()->user()->hasRole('superadmin')) {
            abort(403);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('billing.invoice-pdf', compact('tagihan'));
        return $pdf->download('Invoice-' . $tagihan->no_invoice . '.pdf');
    }
}