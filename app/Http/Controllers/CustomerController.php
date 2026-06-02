<?php

namespace App\Http\Controllers;

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

class CustomerController extends Controller
{
    // Homepage catalog: lists menus and categories
    public function index(Request $request)
    {
        $selected_category = $request->query('kategori');
        $categories = KategoriMenu::all();
        
        if ($selected_category) {
            $menus = Menu::where('id_kategori', $selected_category)->get();
        } else {
            $menus = Menu::with('kategori')->get();
        }

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

    // Add item to local session cart
    public function cartAdd(Request $request)
    {
        $menuId = $request->menu_id;
        $namaMenu = $request->nama_menu;
        $harga = $request->harga;
        $stok = $request->stok;

        $cart = session()->get('cart', []);

        if (isset($cart[$menuId])) {
            if ($cart[$menuId]['qty'] + 1 > $stok) {
                return response()->json(['message' => 'Stok menu tidak mencukupi.'], 400);
            }
            $cart[$menuId]['qty']++;
        } else {
            if (1 > $stok) {
                return response()->json(['message' => 'Stok menu kosong.'], 400);
            }
            $cart[$menuId] = [
                'menu_id' => $menuId,
                'nama_menu' => $namaMenu,
                'harga' => $harga,
                'qty' => 1,
                'stok' => $stok
            ];
        }

        session()->put('cart', $cart);

        return response()->json([
            'message' => 'Menu berhasil ditambahkan ke keranjang',
            'cart' => $cart,
            'total_items' => count($cart)
        ]);
    }

    // Update cart item quantity
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

    // Remove item from cart
    public function cartRemove(Request $request)
    {
        $menuId = $request->menu_id;
        $cart = session()->get('cart', []);

        if (isset($cart[$menuId])) {
            unset($cart[$menuId]);
            session()->put('cart', $cart);
        }

        return response()->json([
            'message' => 'Menu dihapus dari keranjang',
            'cart' => $cart
        ]);
    }

    // Show reservation booking form
    public function showReservasi()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('beranda')->with('error', 'Keranjang belanja Anda kosong.');
        }

        // Get available tables for today initially (default date)
        $tanggal = date('Y-m-d');
        $bookedTableIds = Reservasi::whereDate('tanggal_reservasi', $tanggal)
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('meja_id')
            ->toArray();

        $tables = Meja::where('status', 'tersedia')->get()->map(function($table) use ($bookedTableIds) {
            $table->is_booked = in_array($table->meja_id, $bookedTableIds);
            return $table;
        });

        return view('reservasi', [
            'cart' => $cart,
            'tables' => $tables
        ]);
    }

    // Fetch available tables dynamically via ajax
    public function getAvailableTablesAjax(Request $request)
    {
        $jumlahOrang = $request->query('jumlah_orang');
        $tanggal = $request->query('tanggal_reservasi', date('Y-m-d'));

        // Find tables booked on this date
        $bookedTableIds = Reservasi::whereDate('tanggal_reservasi', $tanggal)
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('meja_id')
            ->toArray();

        $query = Meja::where('status', 'tersedia');

        if ($request->has('jumlah_orang') && !empty($jumlahOrang)) {
            $query->where('kapasitas', '>=', $jumlahOrang);
        }

        $tables = $query->get()->map(function($table) use ($bookedTableIds) {
            $table->is_booked = in_array($table->meja_id, $bookedTableIds);
            return $table;
        });

        return response()->json($tables);
    }

    // Store reservation to DB
    public function storeReservasi(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('beranda')->with('error', 'Keranjang belanja Anda kosong.');
        }

        $validator = Validator::make($request->all(), [
            'tanggal_reservasi' => 'required|date|after_or_equal:today',
            'jumlah_orang' => 'required|integer|min:1',
            'meja_id' => 'required|exists:meja,meja_id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $meja = Meja::findOrFail($request->meja_id);
        if ($meja->status !== 'tersedia') {
            return back()->withErrors(['meja_id' => 'Meja yang dipilih tidak aktif atau tidak tersedia.'])->withInput();
        }

        // Validate table is not booked on this date
        $isBooked = Reservasi::whereDate('tanggal_reservasi', $request->tanggal_reservasi)
            ->where('meja_id', $request->meja_id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();
        if ($isBooked) {
            return back()->withErrors(['meja_id' => 'Meja yang dipilih sudah dipesan pada tanggal tersebut.'])->withInput();
        }

        // Calculate total harga and validate menu stock
        $totalHarga = 0;
        $orderItemsData = [];

        foreach ($cart as $item) {
            $menu = Menu::findOrFail($item['menu_id']);
            if ($menu->stok < $item['qty']) {
                return back()->withErrors([
                    'tanggal_reservasi' => 'Stok untuk menu "' . $menu->nama_menu . '" tidak mencukupi. Tersedia: ' . $menu->stok
                ])->withInput();
            }

            $subtotal = $menu->harga * $item['qty'];
            $totalHarga += $subtotal;

            $orderItemsData[] = [
                'menu' => $menu,
                'qty' => $item['qty'],
                'harga' => $menu->harga,
                'subtotal' => $subtotal
            ];
        }

        // Create Reservasi
        $reservasi = Reservasi::create([
            'user_id' => Auth::user()->user_id,
            'meja_id' => $meja->meja_id,
            'tanggal_reservasi' => $request->tanggal_reservasi,
            'jumlah_orang' => $request->jumlah_orang,
            'status' => 'pending',
            'total_harga' => $totalHarga
        ]);

        // Create Order Items and update menu stocks
        foreach ($orderItemsData as $data) {
            OrderItem::create([
                'reservasi_id' => $reservasi->reservasi_id,
                'menu_id' => $data['menu']->menu_id,
                'jumlah' => $data['qty'],
                'harga' => $data['harga'],
                'subtotal' => $data['subtotal']
            ]);

            $menu = $data['menu'];
            $menu->stok = max(0, $menu->stok - $data['qty']);
            $menu->save();
        }

        session()->forget('cart');
        return redirect()->route('pembayaran')->with('success', 'Reservasi berhasil dibuat. Silakan lakukan pembayaran.');
    }

    // Show payments and reservations dashboard for customer
    public function showPembayaran()
    {
        $reservations = Reservasi::with(['meja', 'order_items.menu', 'pembayaran', 'feedback'])
            ->where('user_id', Auth::user()->user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pembayaran', [
            'reservations' => $reservations
        ]);
    }

    // Store payment upload to DB
    public function storePembayaran(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'metode' => 'required|in:tunai,qris',
            'bukti_bayar' => 'required_if:metode,qris|image|max:2048'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $reservasi = Reservasi::findOrFail($id);

        if ($reservasi->pembayaran) {
            return back()->with('error', 'Pembayaran untuk reservasi ini sudah dilakukan.');
        }

        $buktiBayarPath = null;
        if ($request->metode === 'qris' && $request->hasFile('bukti_bayar')) {
            $file = $request->file('bukti_bayar');
            $filename = time() . '_' . $id . '.' . $file->getClientOriginalExtension();
            $file->storeAs('bukti_bayar', $filename, 'public');
            $buktiBayarPath = 'bukti_bayar/' . $filename;
        }

        Pembayaran::create([
            'reservasi_id' => $reservasi->reservasi_id,
            'metode' => $request->metode,
            'bukti_bayar' => $buktiBayarPath,
            'status' => 'pending',
            'tanggal_bayar' => now()
        ]);

        return redirect()->route('pembayaran')->with('success', 'Bukti pembayaran berhasil dikirim.');
    }

    // Store feedback rating/comment to DB
    public function storeFeedback(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $reservasi = Reservasi::findOrFail($id);

        if ($reservasi->feedback) {
            return back()->with('error', 'Anda sudah memberikan feedback untuk reservasi ini.');
        }

        Feedback::create([
            'reservasi_id' => $reservasi->reservasi_id,
            'user_id' => Auth::user()->user_id,
            'rating' => $request->rating,
            'komentar' => $request->komentar,
            'tanggal' => now()
        ]);

        return redirect()->route('pembayaran')->with('success', 'Feedback berhasil dikirim. Terima kasih!');
    }
}
