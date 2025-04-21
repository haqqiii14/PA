<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    protected $table = 'presensi';

    protected $fillable = [
        'user_id',
        'qr_code', 
        'latitude', 
        'longitude', 
        'waktu_presensi',
        'waktu_pulang', 
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_presensi' => 'datetime',
        'waktu_pulang' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}


