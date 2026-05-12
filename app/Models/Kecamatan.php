<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'kecamatan_id');
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'kecamatan_id');
    }

    public $incrementing = false;

    protected $fillable = ['city_id', 'name'];
}
