<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profil extends Model
{
    protected $table = 'profil';

    protected $fillable = [
        'nama_lengkap',
        'jabatan',
        'ringkasan',
        'biografi',
        'url_foto',
        'status_ketersediaan',
        'email',
        'telepon',
        'url_linkedin',
        'url_github',
        'url_instagram',
        'url_whatsapp',
        'lokasi',
    ];
}
