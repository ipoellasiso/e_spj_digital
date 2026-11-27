<?php
namespace App\Http\Controllers;

use App\Models\Tagihan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SuperadminPendapatanController extends Controller
{
    public function index()
    {
        // Total Pendapatan Keseluruhan (Lunas)
        $totalPendapatan = Tagihan::where('status', 'paid')->sum('total_bayar');
        
        // Pendapatan Bulan Ini
        $pendapatanBulanIni = Tagihan::where('status', 'paid')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->sum('total_bayar');

        // Pendapatan Tahun Ini
        $pendapatanTahunIni = Tagihan::where('status', 'paid')
            ->whereYear('updated_at', now()->year)
            ->sum('total_bayar');

        // Data Chart 12 Bulan Terakhir
        $chartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $total = Tagihan::where('status', 'paid')
                ->whereMonth('updated_at', $month->month)
                ->whereYear('updated_at', $month->year)
                ->sum('total_bayar');
            $chartData[] = $total;
        }

        return view('superadmin.pendapatan.index', compact(
            'totalPendapatan', 'pendapatanBulanIni', 'pendapatanTahunIni', 'chartData'
        ));
    }
}