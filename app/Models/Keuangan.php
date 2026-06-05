<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keuangan extends Model
{
    use HasFactory;

    protected $fillable = [
        'keterangan',
        'nominal',
        'tipe_laporan',
        'tanggal',
        'is_delete',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_delete' => 'boolean',
        'nominal' => 'decimal:2',
    ];
}
