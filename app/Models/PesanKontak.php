<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesanKontak extends Model
{
    protected $table = 'pesan_kontak';

    protected $fillable = [
        'nama',
        'email',
        'subjek',
        'pesan',
        'status',
    ];
}
