<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proyek extends Model
{
    protected $table = 'proyek';

    protected $fillable = [
        'judul',
        'slug',
        'kategori',
        'ringkasan',
        'pernyataan_masalah',
        'ringkasan_solusi',
        'tumpukan_teknologi',
        'fitur_utama',
        'ringkasan_arsitektur',
        'url_gambar_pratinjau',
        'unggulan',
        'status',
        'url_demo',
        'url_github',
        'hasil_proyek',
        'diterbitkan_pada',
    ];

    protected $casts = [
        'tumpukan_teknologi' => 'array',
        'fitur_utama' => 'array',
        'unggulan' => 'boolean',
        'diterbitkan_pada' => 'datetime',
    ];

    /**
     * @return HasMany<GambarProyek, $this>
     */
    public function gambar(): HasMany
    {
        return $this->hasMany(GambarProyek::class, 'proyek_id');
    }
}
