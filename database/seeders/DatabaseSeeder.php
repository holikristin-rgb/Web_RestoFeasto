<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\KategoriMenu;
use App\Models\Menu;
use App\Models\Meja;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Settings
        DB::table('settings')->insert([
            'key' => 'qris_image',
            'value' => 'qris/default_qris.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Seed Users (Admin, Kasir, Pelanggan)
        User::create([
            'nama' => 'Super Admin',
            'email' => 'admin@restofeasto.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'no_hp' => '081234567890',
            'is_verified' => true
        ]);

        User::create([
            'nama' => 'Kasir Resto',
            'email' => 'kasir@restofeasto.com',
            'password' => Hash::make('kasir123'),
            'role' => 'kasir',
            'no_hp' => '081234567891',
            'is_verified' => true
        ]);

        User::create([
            'nama' => 'Test Pelanggan',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'role' => 'pelanggan',
            'no_hp' => '081234567892',
            'is_verified' => true
        ]);

        // 2. Seed Categories
        $makanan = KategoriMenu::create(['nama_kategori' => 'Makanan']);
        $minuman = KategoriMenu::create(['nama_kategori' => 'Minuman']);

        // 3. Seed Menus
        Menu::create([
            'id_kategori' => $makanan->id_kategori,
            'nama_menu' => 'Nasi Goreng Rendang',
            'harga' => 35000,
            'stok' => 50,
            'deskripsi' => 'Perpaduan nasi goreng gurih dengan potongan daging rendang autentik.'
        ]);

        Menu::create([
            'id_kategori' => $makanan->id_kategori,
            'nama_menu' => 'Sate Ayam Madura',
            'harga' => 28000,
            'stok' => 40,
            'deskripsi' => '10 tusuk sate ayam empuk dengan bumbu kacang kental yang menggoda.'
        ]);

        Menu::create([
            'id_kategori' => $makanan->id_kategori,
            'nama_menu' => 'Rendang Daging Sapi',
            'harga' => 45000,
            'stok' => 30,
            'deskripsi' => 'Daging sapi pilihan yang dimasak lambat dengan rempah-rempah tradisional khas Minang.'
        ]);

        Menu::create([
            'id_kategori' => $makanan->id_kategori,
            'nama_menu' => 'Ayam Goreng Kalasan',
            'harga' => 32000,
            'stok' => 35,
            'deskripsi' => 'Ayam goreng khas Sleman dengan rasa manis gurih yang meresap hingga ke dalam.'
        ]);

        Menu::create([
            'id_kategori' => $minuman->id_kategori,
            'nama_menu' => 'Es Dawet Ayu',
            'harga' => 15000,
            'stok' => 100,
            'deskripsi' => 'Minuman segar dengan santan murni dan gula merah asli pilihan.'
        ]);

        Menu::create([
            'id_kategori' => $minuman->id_kategori,
            'nama_menu' => 'Es Teh Manis',
            'harga' => 6000,
            'stok' => 200,
            'deskripsi' => 'Teh melati seduh segar disajikan dingin dengan gula tebu cair.'
        ]);

        Menu::create([
            'id_kategori' => $minuman->id_kategori,
            'nama_menu' => 'Kopi Tubruk',
            'harga' => 12000,
            'stok' => 150,
            'deskripsi' => 'Kopi hitam murni robusta dengan seduhan tradisional yang pekat dan mantap.'
        ]);

        // 4. Seed Tables (Meja)
        Meja::create(['nomor_meja' => 'Meja 01', 'kapasitas' => 2, 'status' => 'tersedia']);
        Meja::create(['nomor_meja' => 'Meja 02', 'kapasitas' => 2, 'status' => 'tersedia']);
        Meja::create(['nomor_meja' => 'Meja 03', 'kapasitas' => 4, 'status' => 'tersedia']);
        Meja::create(['nomor_meja' => 'Meja 04', 'kapasitas' => 4, 'status' => 'tersedia']);
        Meja::create(['nomor_meja' => 'Meja 05', 'kapasitas' => 6, 'status' => 'tersedia']);
        Meja::create(['nomor_meja' => 'Meja 06', 'kapasitas' => 8, 'status' => 'tersedia']);
    }
}
