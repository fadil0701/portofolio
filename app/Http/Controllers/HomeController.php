<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\KategoriKeahlian;
use App\Models\PengalamanKerja;
use App\Models\Profil;
use App\Models\Proyek;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $profil = Profil::query()->first();
        $jumlahProyek = Proyek::query()
            ->where('status', 'published')
            ->count();
        $proyek = Proyek::query()
            ->where('status', 'published')
            ->orderByDesc('unggulan')
            ->orderByDesc('diterbitkan_pada')
            ->take(3)
            ->get();

        $kategoriKeahlian = KategoriKeahlian::query()
            ->with('keahlian')
            ->orderBy('urutan')
            ->get();
        $jumlahKeahlian = $kategoriKeahlian->sum(fn (KategoriKeahlian $kategori): int => $kategori->keahlian->count());

        $pengalaman = PengalamanKerja::query()
            ->orderByDesc('tanggal_mulai')
            ->get();

        $artikel = Artikel::query()
            ->where('status', 'published')
            ->orderByDesc('diterbitkan_pada')
            ->take(3)
            ->get();

        return view('home', compact(
            'profil',
            'proyek',
            'jumlahProyek',
            'kategoriKeahlian',
            'jumlahKeahlian',
            'pengalaman',
            'artikel',
        ));
    }
}
