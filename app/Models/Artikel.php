<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Artikel extends Model
{
    protected $table = 'artikel';

    protected $fillable = [
        'judul',
        'slug',
        'kategori',
        'ringkasan',
        'isi',
        'url_gambar_utama',
        'waktu_baca',
        'unggulan',
        'status',
        'judul_seo',
        'deskripsi_seo',
        'diterbitkan_pada',
    ];

    protected $casts = [
        'unggulan' => 'boolean',
        'diterbitkan_pada' => 'datetime',
    ];

    /**
     * @return BelongsToMany<TagArtikel, $this>
     */
    public function tagArtikel(): BelongsToMany
    {
        return $this->belongsToMany(TagArtikel::class, 'artikel_tag', 'artikel_id', 'tag_artikel_id');
    }
}
