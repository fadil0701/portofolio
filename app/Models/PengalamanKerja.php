<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengalamanKerja extends Model
{
    protected $table = 'pengalaman_kerja';

    protected $fillable = [
        'organisasi',
        'jabatan',
        'jenis_pekerjaan',
        'tanggal_mulai',
        'tanggal_selesai',
        'deskripsi',
        'tanggung_jawab',
        'pencapaian',
        'teknologi',
        'urutan',
    ];

    protected $casts = [
        'tanggung_jawab' => 'array',
        'pencapaian' => 'array',
        'teknologi' => 'array',
    ];
}
