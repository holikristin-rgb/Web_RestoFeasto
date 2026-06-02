<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservasi extends Model
{
    protected $table = 'reservasi';
    protected $primaryKey = 'reservasi_id';

    protected $fillable = [
        'user_id',
        'meja_id',
        'tanggal_reservasi',
        'jumlah_orang',
        'status',
        'total_harga'
    ];

    /**
     * Relationship to User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relationship to Meja.
     */
    public function meja()
    {
        return $this->belongsTo(Meja::class, 'meja_id', 'meja_id');
    }

    /**
     * Relationship to OrderItems.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'reservasi_id', 'reservasi_id');
    }

    public function order_items()
    {
        return $this->hasMany(OrderItem::class, 'reservasi_id', 'reservasi_id');
    }

    /**
     * Relationship to Pembayaran.
     */
    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'reservasi_id', 'reservasi_id');
    }

    /**
     * Relationship to Feedback.
     */
    public function feedback()
    {
        return $this->hasOne(Feedback::class, 'reservasi_id', 'reservasi_id');
    }
}
