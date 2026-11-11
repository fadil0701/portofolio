<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Keahlian extends Model
{
    protected $table = 'keahlian';

    protected $fillable = [
        'kategori_keahlian_id',
        'nama',
        'deskripsi',
        'tingkat',
        'ikon',
        'urutan',
    ];

    /**
     * @return BelongsTo<KategoriKeahlian, $this>
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriKeahlian::class, 'kategori_keahlian_id');
    }
}
