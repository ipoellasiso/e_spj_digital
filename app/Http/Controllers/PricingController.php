<?php

namespace App\Http\Controllers;

use App\Models\Paket;

class PricingController extends Controller
{
    public function index()
    {
        $pakets = Paket::orderBy('harga_bulanan')->get();
        return view('pricing', compact('pakets'));
    }
}
