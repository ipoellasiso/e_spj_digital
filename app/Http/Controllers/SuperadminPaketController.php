<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paket;
use App\Models\Tagihan;

class SuperadminPaketController extends Controller
{
    public function index()
    {
        $pakets = Paket::orderBy('harga_bulanan')->get();
        return view('superadmin.paket.index', compact('pakets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'harga_bulanan' => 'required|numeric',
            'harga_tahunan' => 'nullable|numeric',
            'fitur' => 'required|string', // Input teks pisah koma
            'is_populer' => 'boolean'
        ]);

        // Ubah teks fitur yang dipisah koma menjadi array json
        $fiturArray = array_map('trim', explode(',', $request->fitur));

        Paket::create([
            'nama' => $request->nama,
            'harga_bulanan' => $request->harga_bulanan,
            'harga_tahunan' => $request->harga_tahunan,
            'fitur' => $fiturArray,
            'is_populer' => $request->has('is_populer'),
        ]);

        return back()->with('sukses', 'Paket berhasil ditambahkan.');
    }

        public function buatTagihan(Request $request)
    {
        $request->validate([
            'opd_id' => 'required|exists:opd,id',
            'paket_id' => 'required|exists:pakets,id',
            'durasi' => 'required|in:bulanan,tahunan', // Validasi durasi
        ]);

        $paket = Paket::findOrFail($request->paket_id);
        
        // Tentukan total bayar dan masa aktif
        $totalBayar = $request->durasi === 'tahunan' ? $paket->harga_tahunan : $paket->harga_bulanan;
        $masaAktif = $request->durasi === 'tahunan' ? now()->addYear() : now()->addMonth();

        Tagihan::create([
            'opd_id' => $request->opd_id,
            'paket_id' => $paket->id,
            'no_invoice' => 'INV-' . date('Ymd') . '-' . rand(100, 999),
            'total_bayar' => $totalBayar,
            'periode_mulai' => now(),
            'periode_berakhir' => $masaAktif,
            'status' => 'unpaid'
        ]);

        return back()->with('sukses', 'Tagihan berhasil dibuat untuk OPD tersebut.');
    }
}