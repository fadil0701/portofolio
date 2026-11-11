<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriKeahlian extends Model
{
    protected $table = 'kategori_keahlian';

    protected $fillable = [
        'nama',
        'slug',
        'urutan',
    ];

    /**
     * @return HasMany<Keahlian, $this>
     */
    public function keahlian(): HasMany
    {
        return $this->hasMany(Keahlian::class, 'kategori_keahlian_id')->orderBy('urutan');
    }
}
