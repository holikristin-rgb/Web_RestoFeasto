<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriMenu extends Model
{
    protected $table = 'kategori_menu';
    protected $primaryKey = 'id_kategori';
    
    protected $fillable = [
        'nama_kategori'
    ];

    /**
     * Relationship to Menu.
     */
    public function menus()
    {
        return $this->hasMany(Menu::class, 'id_kategori', 'id_kategori');
    }
}
