<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TagArtikel extends Model
{
    protected $table = 'tag_artikel';

    protected $fillable = [
        'nama',
        'slug',
    ];

    /**
     * @return BelongsToMany<Artikel, $this>
     */
    public function artikel(): BelongsToMany
    {
        return $this->belongsToMany(Artikel::class, 'artikel_tag', 'tag_artikel_id', 'artikel_id');
    }
}
