<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Menu;
use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Models\Feedback;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function index()
    {
        $menus = Menu::with('kategori')->get();
        $categories = KategoriMenu::all();
        $tables = Meja::all();
        $staff = User::whereIn('role', ['admin', 'kasir'])->get();
        $feedbacks = Feedback::with(['user', 'reservasi.meja'])->orderBy('tanggal', 'desc')->get();
        $qris_image = Setting::getValue('qris_image', 'qris/default_qris.jpg');

        return view('admin.index', compact('menus', 'categories', 'tables', 'staff', 'feedbacks', 'qris_image'));
    }

    // --- Menu CRUD ---
    public function storeMenu(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_menu' => 'required|string|max:150',
            'id_kategori' => 'required|exists:kategori_menu,id_kategori',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'deskripsi' => 'required|string',
            'gambar' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('menus', $filename, 'public');
            $gambarPath = 'menus/' . $filename;
        }

        Menu::create([
            'nama_menu' => $request->nama_menu,
            'id_kategori' => $request->id_kategori,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'deskripsi' => $request->deskripsi,
            'gambar' => $gambarPath,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function updateMenu(Request $request, $menu_id)
    {
        $menu = Menu::findOrFail($menu_id);

        $validator = Validator::make($request->all(), [
            'nama_menu' => 'required|string|max:150',
            'id_kategori' => 'required|exists:kategori_menu,id_kategori',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'deskripsi' => 'required|string',
            'gambar' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->only(['nama_menu', 'id_kategori', 'harga', 'stok', 'deskripsi']);

        if ($request->hasFile('gambar')) {
            // Delete old image if exists
            if ($menu->gambar) {
                Storage::disk('public')->delete($menu->gambar);
            }
            $file = $request->file('gambar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('menus', $filename, 'public');
            $data['gambar'] = 'menus/' . $filename;
        }

        $menu->update($data);

        return redirect()->route('admin.dashboard')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroyMenu($menu_id)
    {
        $menu = Menu::findOrFail($menu_id);
        if ($menu->gambar) {
            Storage::disk('public')->delete($menu->gambar);
        }
        $menu->delete();
        
        return redirect()->route('admin.dashboard')->with('success', 'Menu berhasil dihapus.');
    }

    // --- Category CRUD ---
    public function storeCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:100|unique:kategori_menu,nama_kategori',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        KategoriMenu::create($request->all());

        return redirect()->route('admin.dashboard')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, $id)
    {
        $category = KategoriMenu::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:100|unique:kategori_menu,nama_kategori,' . $id . ',id_kategori',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $category->update($request->all());

        return redirect()->route('admin.dashboard')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroyCategory($id)
    {
        $category = KategoriMenu::findOrFail($id);
        $category->delete();
        
        return redirect()->route('admin.dashboard')->with('success', 'Kategori berhasil dihapus.');
    }

    // --- Meja (Table) CRUD ---
    public function storeMeja(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomor_meja' => 'required|string|max:20|unique:meja,nomor_meja',
            'kapasitas' => 'required|integer|min:1',
            'status' => 'required|in:tersedia,dipesan',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Meja::create($request->all());

        return redirect()->route('admin.dashboard')->with('success', 'Meja berhasil ditambahkan.');
    }

    public function updateMeja(Request $request, $id)
    {
        $meja = Meja::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nomor_meja' => 'required|string|max:20|unique:meja,nomor_meja,' . $id . ',meja_id',
            'kapasitas' => 'required|integer|min:1',
            'status' => 'required|in:tersedia,dipesan',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $meja->update($request->all());

        return redirect()->route('admin.dashboard')->with('success', 'Meja berhasil diperbarui.');
    }

    public function destroyMeja($id)
    {
        $meja = Meja::findOrFail($id);
        $meja->delete();
        
        return redirect()->route('admin.dashboard')->with('success', 'Meja berhasil dihapus.');
    }

    // --- Staff CRUD ---
    public function storeStaff(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:150',
            'email' => 'required|string|email|max:150|unique:users,email',
            'no_hp' => 'required|string|max:20',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,kasir',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Staf baru berhasil didaftarkan.');
    }

    public function updateStaff(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:150',
            'email' => 'required|string|email|max:150|unique:users,email,' . $id . ',user_id',
            'no_hp' => 'required|string|max:20',
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,kasir',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $role = $request->role;
        // Super Admin account protection
        if ($user->email === 'admin@restofeasto.com') {
            $role = 'admin';
        }

        $user->nama = $request->nama;
        $user->email = $request->email;
        $user->no_hp = $request->no_hp;
        $user->role = $role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.dashboard')->with('success', 'Staf berhasil diperbarui.');
    }

    public function destroyStaff($id)
    {
        $user = User::findOrFail($id);

        if ($user->email === 'admin@restofeasto.com') {
            return back()->with('error', 'Akun Super Admin tidak boleh dihapus.');
        }

        $user->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Staf berhasil dihapus.');
    }

    public function updateQris(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qris_image' => 'required|image|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        if ($request->hasFile('qris_image')) {
            $file = $request->file('qris_image');
            $filename = 'qris_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('qris', $filename, 'public');
            
            // Save to settings table
            Setting::setValue('qris_image', 'qris/' . $filename);
            
            return back()->with('success', 'QRIS berhasil diperbarui.');
        }

        return back()->withErrors(['qris_image' => 'Gagal mengunggah gambar QRIS.']);
    }
}
