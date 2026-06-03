<?php

namespace App\Http\Controllers;

// Pastikan semua Model diimport dengan benar
use App\Models\Menu;
use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Models\Reservasi;
use App\Models\OrderItem;
use App\Models\Pembayaran;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; // Tambahan untuk keamanan database

class CustomerController extends Controller
{
    /**
     * Homepage catalog: lists menus and categories
     */
    public function index(Request $request)
    {
        $selected_category = $request->query('kategori');
        $categories = KategoriMenu::all();
        
        // Menggunakan query builder agar lebih rapi
        $query = Menu::query();

        if ($selected_category) {
            $query->where('id_kategori', $selected_category);
        }

        $menus = $query->with('kategori')->get();

        $feedbacks = Feedback::with('user')
            ->orderBy('tanggal', 'desc')
            ->take(3)
            ->get();

        return view('welcome', [
            'categories' => $categories,
            'menus' => $menus,
            'selected_category' => $selected_category,
            'is_logged_in' => Auth::check(),
            'feedbacks' => $feedbacks
        ]);
    }

    /**
     * Add item to local session cart
     */
    public function cartAdd(Request $request)
    {
        $menuId = $request->menu_id;
        $menu = Menu::find($menuId); // Validasi menu ada di DB

        if (!$menu) {
            return response()->json(['message' => 'Menu tidak ditemukan.'], 404);
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$menuId])) {
            if ($cart[$menuId]['qty'] + 1 > $menu->stok) {
                return response()->json(['message' => 'Stok menu tidak mencukupi.'], 400);
            }
            $cart[$menuId]['qty']++;
        } else {
            if ($menu->stok < 1) {
                return response()->json(['message' => 'Stok menu kosong.'], 400);
            }
            $cart[$menuId] = [
                'menu_id' => $menu->menu_id,
                'nama_menu' => $menu->nama_menu,
                'harga' => $menu->harga,
                'qty' => 1,
                'stok' => $menu->stok
            ];
        }

        session()->put('cart', $cart);

        return response()->json([
            'message' => 'Menu berhasil ditambahkan ke keranjang',
            'cart' => $cart,
            'total_items' => count($cart)
        ]);
    }

    public function cartUpdate(Request $request)
    {
        $menuId = $request->menu_id;
        $qty = $request->qty;
        $cart = session()->get('cart', []);

        if (isset($cart[$menuId])) {
            if ($qty <= 0) {
                unset($cart[$menuId]);
            } else {
                if ($qty > $cart[$menuId]['stok']) {
                    return response()->json(['message' => 'Stok tidak mencukupi.'], 400);
                }
                $cart[$menuId]['qty'] = $qty;
            }
            session()->put('cart', $cart);
        }

        return response()->json([
            'message' => 'Keranjang berhasil diperbarui',
            'cart' => $cart
        ]);
    }

    public function cartRemove(Request $request)
    {
        $menuId = $request->menu_id;
        $cart = session()->get('cart', []);

        if (isset($cart[$menuId])) {
            unset($cart[$menuId]);
            session()->put('cart', $cart);
        }

        return response()->json(['message' => 'Menu dihapus', 'cart' => $cart]);
    }

    public function showReservasi()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('beranda')->with('error', 'Keranjang belanja kosong.');
        }

        $tanggal = date('Y-m-d');
        $bookedTableIds = Reservasi::whereDate('tanggal_reservasi', $tanggal)
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('meja_id')
            ->toArray();

        $tables = Meja::where('status', 'tersedia')->get()->map(function($table) use ($bookedTableIds) {
            $table->is_booked = in_array($table->meja_id, $bookedTableIds);
            return $table;
        });

        return view('reservasi', compact('cart', 'tables'));
    }

    public function getAvailableTablesAjax(Request $request)
    {
        $jumlahOrang = $request->query('jumlah_orang');
        $tanggal = $request->query('tanggal_reservasi', date('Y-m-d'));

        $bookedTableIds = Reservasi::whereDate('tanggal_reservasi', $tanggal)
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('meja_id')
            ->toArray();

        $query = Meja::where('status', 'tersedia');
        if (!empty($jumlahOrang)) {
            $query->where('kapasitas', '>=', $jumlahOrang);
        }

        $tables = $query->get()->map(function($table) use ($bookedTableIds) {
            $table->is_booked = in_array($table->meja_id, $bookedTableIds);
            return $table;
        });

        return response()->json($tables);
    }

    public function storeReservasi(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) return redirect()->route('beranda');

        $request->validate([
            'tanggal_reservasi' => 'required|date|after_or_equal:today',
            'jumlah_orang' => 'required|integer|min:1',
            'meja_id' => 'required|exists:meja,meja_id',
        ]);

        // Gunakan Database Transaction agar data konsisten jika stok gagal update
        return DB::transaction(function () use ($request, $cart) {
            $meja = Meja::findOrFail($request->meja_id);
            
            $isBooked = Reservasi::whereDate('tanggal_reservasi', $request->tanggal_reservasi)
                ->where('meja_id', $request->meja_id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists();

            if ($isBooked) return back()->withErrors(['meja_id' => 'Meja sudah dipesan.']);

            $totalHarga = 0;
            foreach ($cart as $item) {
                $menu = Menu::lockForUpdate()->findOrFail($item['menu_id']);
                if ($menu->stok < $item['qty']) {
                    return back()->withErrors(['error' => "Stok $menu->nama_menu habis."]);
                }
                $totalHarga += ($menu->harga * $item['qty']);
            }

            $reservasi = Reservasi::create([
                'user_id' => Auth::id(),
                'meja_id' => $meja->meja_id,
                'tanggal_reservasi' => $request->tanggal_reservasi,
                'jumlah_orang' => $request->jumlah_orang,
                'status' => 'pending',
                'total_harga' => $totalHarga
            ]);

            foreach ($cart as $item) {
                $menu = Menu::find($item['menu_id']);
                OrderItem::create([
                    'reservasi_id' => $reservasi->reservasi_id,
                    'menu_id' => $menu->menu_id,
                    'jumlah' => $item['qty'],
                    'harga' => $menu->harga,
                    'subtotal' => $menu->harga * $item['qty']
                ]);
                $menu->decrement('stok', $item['qty']);
            }

            session()->forget('cart');
            return redirect()->route('pembayaran')->with('success', 'Reservasi berhasil.');
        });
    }

    public function showPembayaran()
    {
        $reservations = Reservasi::with(['meja', 'order_items.menu', 'pembayaran', 'feedback'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pembayaran', compact('reservations'));
    }

    public function storePembayaran(Request $request, $id)
    {
        $request->validate([
            'metode' => 'required|in:tunai,qris',
            'bukti_bayar' => 'required_if:metode,qris|image|max:2048'
        ]);

        $reservasi = Reservasi::findOrFail($id);
        if ($reservasi->pembayaran) return back()->with('error', 'Sudah dibayar.');

        $buktiBayarPath = null;
        if ($request->metode === 'qris' && $request->hasFile('bukti_bayar')) {
            $path = $request->file('bukti_bayar')->store('bukti_bayar', 'public');
            $buktiBayarPath = $path;
        }

        Pembayaran::create([
            'reservasi_id' => $reservasi->reservasi_id,
            'metode' => $request->metode,
            'bukti_bayar' => $buktiBayarPath,
            'status' => 'pending',
            'tanggal_bayar' => now()
        ]);

        return redirect()->route('pembayaran')->with('success', 'Bukti terkirim.');
    }

    public function storeFeedback(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'required|string',
        ]);

        $reservasi = Reservasi::findOrFail($id);
        if ($reservasi->feedback) return back()->with('error', 'Feedback sudah ada.');

        Feedback::create([
            'reservasi_id' => $reservasi->reservasi_id,
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'komentar' => $request->komentar,
            'tanggal' => now()
        ]);

        return redirect()->route('pembayaran')->with('success', 'Terima kasih atas feedbacknya!');
    }
}