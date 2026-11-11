<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GambarProyek extends Model
{
    protected $table = 'gambar_proyek';

    protected $fillable = [
        'proyek_id',
        'url_gambar',
        'keterangan',
        'urutan',
    ];

    /**
     * @return BelongsTo<Proyek, $this>
     */
    public function proyek(): BelongsTo
    {
        return $this->belongsTo(Proyek::class, 'proyek_id');
    }
}
