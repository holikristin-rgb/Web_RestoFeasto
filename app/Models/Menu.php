<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';
    protected $primaryKey = 'menu_id';
    
    protected $fillable = [
        'id_kategori',
        'nama_menu',
        'harga',
        'stok',
        'deskripsi',
        'gambar'
    ];

    /**
     * Relationship to KategoriMenu.
     */
    public function kategori()
    {
        return $this->belongsTo(KategoriMenu::class, 'id_kategori', 'id_kategori');
    }

    /**
     * Relationship to OrderItems.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'menu_id', 'menu_id');
    }

    public function order_items()
    {
        return $this->hasMany(OrderItem::class, 'menu_id', 'menu_id');
    }
}
