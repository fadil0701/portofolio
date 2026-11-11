<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Models\Keahlian;
use App\Models\PesanKontak;
use App\Models\Proyek;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'jumlahProyek' => Proyek::query()->count(),
            'jumlahArtikel' => Artikel::query()->count(),
            'jumlahKeahlian' => Keahlian::query()->count(),
            'pesanBaru' => PesanKontak::query()->where('status', 'new')->count(),
            'proyekTerbaru' => Proyek::query()->latest()->take(5)->get(),
        ]);
    }
}
