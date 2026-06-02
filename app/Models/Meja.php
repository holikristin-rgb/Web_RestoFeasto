<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meja extends Model
{
    protected $table = 'meja';
    protected $primaryKey = 'meja_id';

    protected $fillable = [
        'nomor_meja',
        'kapasitas',
        'status'
    ];

    /**
     * Relationship to Reservasi.
     */
    public function reservasi()
    {
        return $this->hasMany(Reservasi::class, 'meja_id', 'meja_id');
    }
}
