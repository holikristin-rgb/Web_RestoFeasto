<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedback';
    protected $primaryKey = 'feedback_id';

    protected $fillable = [
        'reservasi_id',
        'user_id',
        'rating',
        'komentar',
        'tanggal'
    ];

    /**
     * Relationship to Reservasi.
     */
    public function reservasi()
    {
        return $this->belongsTo(Reservasi::class, 'reservasi_id', 'reservasi_id');
    }

    /**
     * Relationship to User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
