<?php

namespace App\Http\Controllers;

use App\Models\Reservasi;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KasirController extends Controller
{
    public function index()
    {
        $reservations = Reservasi::with(['user', 'meja', 'order_items.menu', 'pembayaran'])
            ->orderBy('tanggal_reservasi', 'desc')
            ->get();

        return view('kasir.index', compact('reservations'));
    }

    public function confirmBooking(Request $request, $reservasi_id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:confirmed,cancelled,completed'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $reservasi = Reservasi::findOrFail($reservasi_id);
        $reservasi->status = $request->status;
        $reservasi->save();

        return redirect()->route('kasir.dashboard')->with('success', 'Status reservasi berhasil diperbarui.');
    }

    public function confirmPayment(Request $request, $pembayaran_id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:diterima,ditolak'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $pembayaran = Pembayaran::findOrFail($pembayaran_id);
        $pembayaran->status = $request->status;
        $pembayaran->save();

        $reservasi = $pembayaran->reservasi;

        // If payment is accepted, automatically confirm the reservation
        if ($request->status === 'diterima' && $reservasi) {
            $reservasi->status = 'confirmed';
            $reservasi->save();
        }

        return redirect()->route('kasir.dashboard')->with('success', 'Status pembayaran berhasil diperbarui.');
    }
}
