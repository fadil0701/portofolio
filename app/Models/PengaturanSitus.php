<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanSitus extends Model
{
    protected $table = 'pengaturan_situs';

    protected $fillable = [
        'nama_situs',
        'slogan',
        'email_utama',
        'url_linkedin',
        'url_github',
        'url_instagram',
        'url_whatsapp',
        'url_logo',
        'url_favicon',
        'mode_pemeliharaan',
    ];

    protected $casts = [
        'mode_pemeliharaan' => 'boolean',
    ];
}
