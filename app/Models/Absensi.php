<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $fillable = ['user_id', 'qr_code', 'latitude', 'longitude', 'waktu_absen'];
}
