<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Opd;
use App\Models\User;
use App\Models\Paket;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function form(Request $request)
    {
        $paket_id = $request->query('paket_id');
        $daftarPaket = Paket::all();
        return view('auth.register', compact('daftarPaket', 'paket_id'));
    }

    public function store(Request $request)
    {
        // 1. Tambahkan validasi untuk kode dan singkatan
        $request->validate([
            'kode' => 'required|string|max:40|unique:opd,kode', 
            'nama_opd' => 'required|string|max:255',
            'singkatan' => 'required|string|max:30', // Maksimal 30 sesuai database
            'nama_admin' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'paket_id' => 'required|exists:pakets,id',
        ]);

        DB::beginTransaction();

        try {
            // 2. Gunakan input dari user, bukan generate otomatis
            $opd = Opd::create([
                'kode' => $request->kode, 
                'nama' => $request->nama_opd,
                'singkatan' => $request->singkatan,
                'alamat' => '-',
            ]);

            $user = User::create([
                'opd_id' => $opd->id,
                'name' => $request->nama_admin,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'aktif' => true,
            ]);
            
            $user->assignRole('admin'); 

            // 3. Terbitkan Tagihan Pertama otomatis
            $paket = Paket::findOrFail($request->paket_id);
            
            // --- PERBAIKAN NO INVOICE ---
            // Gunakan format: INV-Ymd-His-Random(3 digit)
            // Contoh: INV-20261125-145931-123
            $noInvoice = 'INV-' . date('Ymd-His') . '-' . rand(100, 999);
            
            Tagihan::create([
                'opd_id' => $opd->id,
                'paket_id' => $paket->id,
                'no_invoice' => $noInvoice, // <-- Gunakan variabel yang baru
                'total_bayar' => $paket->harga_bulanan,
                'periode_mulai' => now(),
                'periode_berakhir' => now()->addMonth(),
                'status' => 'unpaid'
            ]);

            DB::commit();

            return redirect()->route('login')->with('sukses', 'Pendaftaran berhasil! Silakan login untuk melakukan pembayaran tagihan pertama Anda.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat mendaftar: ' . $e->getMessage())->withInput();
        }
    }
}