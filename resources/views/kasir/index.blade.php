@extends('layouts.app')

@section('content')
<section class="container mx-auto px-6 py-12">
    <!-- Dashboard Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-[#4A2C2A]">Panel Kasir RestoFeasto</h1>
            <p class="text-gray-500">Konfirmasi reservasi meja makan dan verifikasi bukti pembayaran QRIS</p>
        </div>
        <div class="bg-[#FFF5EE] px-4 py-2 border border-orange-100 rounded-2xl flex items-center gap-2">
            <span class="w-2.5 h-2.5 bg-green-500 rounded-full animate-ping"></span>
            <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Kasir Online</span>
        </div>
    </div>

    <!-- Stats summary -->
    @php
        $pendingBook = 0; $confBook = 0; $completedBook = 0; $cancelledBook = 0;
        foreach($reservations as $r) {
            if($r->status === 'pending') $pendingBook++;
            elseif($r->status === 'confirmed') $confBook++;
            elseif($r->status === 'completed') $completedBook++;
            elseif($r->status === 'cancelled') $cancelledBook++;
        }
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-10">
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pending</span>
            <span class="text-3xl font-black text-yellow-500 mt-2">{{ $pendingBook }}</span>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Terkonfirmasi</span>
            <span class="text-3xl font-black text-green-500 mt-2">{{ $confBook }}</span>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Selesai</span>
            <span class="text-3xl font-black text-blue-500 mt-2">{{ $completedBook }}</span>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Batal</span>
            <span class="text-3xl font-black text-red-500 mt-2">{{ $cancelledBook }}</span>
        </div>
    </div>

    <!-- Reservations Table -->
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
            <h2 class="font-bold text-[#4A2C2A] uppercase tracking-wider text-sm">Daftar Pemesanan & Reservasi</h2>
            <span class="text-xs text-gray-400 font-semibold">Total Data: {{ count($reservations) }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50/50 border-b border-gray-100">
                    <tr class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                        <th class="px-6 py-4">ID / Pelanggan</th>
                        <th class="px-6 py-4">Meja / Jadwal</th>
                        <th class="px-6 py-4">Pesanan & Total</th>
                        <th class="px-6 py-4">Pembayaran</th>
                        <th class="px-6 py-4 text-center">Aksi Reservasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs font-medium text-gray-700">
                    @forelse($reservations as $res)
                        <tr class="hover:bg-gray-50/50 transition">
                            <!-- User info -->
                            <td class="px-6 py-4 space-y-1">
                                <div class="font-bold text-[#4A2C2A]">#RES-{{ $res->reservasi_id }}</div>
                                <div class="font-semibold text-gray-900 text-xs">{{ $res->user->nama ?? 'Pelanggan' }}</div>
                                <div class="text-[10px] text-gray-400 font-normal">{{ $res->user->email }} / {{ $res->user->no_hp ?? '-' }}</div>
                            </td>

                            <!-- Table & schedule -->
                            <td class="px-6 py-4 space-y-1">
                                <div class="font-bold text-orange-600">Meja: {{ $res->meja->nomor_meja ?? '-' }}</div>
                                <div class="font-semibold">{{ date('d M Y', strtotime($res->tanggal_reservasi)) }}</div>
                                <div class="text-[10px] text-gray-400">{{ $res->jumlah_orang }} Tamu</div>
                            </td>

                            <!-- Orders & total price -->
                            <td class="px-6 py-4 space-y-1.5 max-w-xs">
                                <div class="text-[10px] text-gray-400 font-normal leading-relaxed">
                                    @foreach($res->order_items as $item)
                                        <div>• {{ $item->menu->nama_menu ?? 'Menu' }} (x{{ $item->jumlah }})</div>
                                    @endforeach
                                </div>
                                <div class="font-bold text-[#4A2C2A] text-sm pt-1 border-t border-dashed border-gray-100">
                                    Rp {{ number_format($res->total_harga, 0, ',', '.') }}
                                </div>
                            </td>

                            <!-- Payment status -->
                            <td class="px-6 py-4">
                                @if($res->pembayaran)
                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[9px] uppercase font-black bg-gray-100 px-1.5 py-0.5 rounded text-gray-600">{{ $res->pembayaran->metode }}</span>
                                            @php
                                                $payColors = ['pending' => 'text-yellow-600 bg-yellow-50 border border-yellow-100', 'diterima' => 'text-green-600 bg-green-50 border border-green-100', 'ditolak' => 'text-red-600 bg-red-50 border border-red-100'];
                                                $payClass = $payColors[$res->pembayaran->status] ?? 'text-gray-600';
                                            @endphp
                                            <span class="text-[9px] uppercase font-bold px-2 py-0.5 rounded-full {{ $payClass }}">
                                                {{ $res->pembayaran->status }}
                                            </span>
                                        </div>

                                        @if($res->pembayaran->metode === 'qris')
                                            <button onclick="openModal('{{ $res->pembayaran->pembayaran_id }}', '{{ route('storage.file', ['path' => $res->pembayaran->bukti_bayar]) }}', '{{ $res->pembayaran->status }}')"
                                                    class="text-[10px] bg-orange-100 text-orange-700 hover:bg-orange-200 px-2.5 py-1 rounded font-bold transition shadow-sm uppercase tracking-wide">
                                                Lihat Bukti
                                            </button>
                                        @else
                                            <!-- Tunai flow -->
                                            @if($res->pembayaran->status === 'pending')
                                                <form action="{{ route('kasir.payment.confirm', $res->pembayaran->pembayaran_id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="status" value="diterima">
                                                    <button type="submit" class="text-[10px] bg-green-600 hover:bg-green-700 text-white px-2.5 py-1 rounded font-bold transition shadow-sm uppercase tracking-wider">
                                                        Terima Tunai
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                @else
                                    <span class="text-[10px] font-bold text-gray-400 italic">Belum Mengirim Pembayaran</span>
                                @endif
                            </td>

                            <!-- Reservation actions -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    @if($res->status === 'pending')
                                        <form action="{{ route('kasir.booking.confirm', $res->reservasi_id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold px-3 py-1.5 rounded-xl uppercase text-[10px] tracking-wide transition shadow-sm">
                                                Konfirmasi
                                            </button>
                                        </form>
                                        <form action="{{ route('kasir.booking.confirm', $res->reservasi_id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="border border-red-200 text-red-600 hover:bg-red-50 font-bold px-3 py-1.5 rounded-xl uppercase text-[10px] tracking-wide transition">
                                                Batalkan
                                            </button>
                                        </form>
                                    @elseif($res->status === 'confirmed')
                                        <form action="{{ route('kasir.booking.confirm', $res->reservasi_id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-3 py-1.5 rounded-xl uppercase text-[10px] tracking-wide transition shadow-sm">
                                                Tandai Selesai
                                            </button>
                                        </form>
                                        <form action="{{ route('kasir.booking.confirm', $res->reservasi_id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="border border-red-200 text-red-600 hover:bg-red-50 font-bold px-3 py-1.5 rounded-xl uppercase text-[10px] tracking-wide transition">
                                                Batalkan
                                            </button>
                                        </form>
                                    @else
                                        @php
                                            $stateColors = ['completed' => 'text-blue-700 bg-blue-50 border border-blue-100', 'cancelled' => 'text-red-700 bg-red-50 border border-red-100'];
                                            $stateClass = $stateColors[$res->status] ?? 'text-gray-500 bg-gray-50';
                                        @endphp
                                        <span class="text-[10px] uppercase font-black px-3 py-1 rounded-full {{ $stateClass }}">
                                            Selesai / Batal
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center text-gray-500 font-medium">
                                Belum ada data reservasi atau pemesanan yang masuk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- QRIS Modal View -->
<div id="qris-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div onclick="closeModal()" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- Spacer to center modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal contents -->
        <div class="relative inline-block align-middle bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full border border-gray-100">
            <div class="bg-[#4A2C2A] px-6 py-4 flex justify-between items-center text-white">
                <h3 class="text-sm font-bold uppercase tracking-wider text-[#FFF5EE]">Validasi Bukti Bayar QRIS</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-white transition text-xl">&times;</button>
            </div>
            
            <div class="p-6 flex flex-col items-center">
                <img id="modal-img" src="" alt="Bukti Pembayaran" class="max-w-full max-h-[350px] object-contain rounded-2xl shadow border border-gray-100 p-1 bg-white mb-6">

                <div id="modal-actions" class="w-full flex gap-4 pt-4 border-t border-gray-100">
                    <form id="accept-form" action="" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="status" value="diterima">
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-2xl uppercase tracking-wider text-xs transition shadow">
                            Terima Bukti
                        </button>
                    </form>
                    
                    <form id="reject-form" action="" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="status" value="ditolak">
                        <button type="submit" class="w-full border border-red-200 text-red-600 hover:bg-red-50 font-bold py-3 rounded-2xl uppercase tracking-wider text-xs transition">
                            Tolak Bukti
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal(paymentId, imgSrc, status) {
        const modal = document.getElementById('qris-modal');
        const img = document.getElementById('modal-img');
        const acceptForm = document.getElementById('accept-form');
        const rejectForm = document.getElementById('reject-form');
        const actions = document.getElementById('modal-actions');

        img.src = imgSrc;
        acceptForm.action = `/kasir/payments/${paymentId}/confirm`;
        rejectForm.action = `/kasir/payments/${paymentId}/confirm`;

        if (status === 'pending') {
            actions.classList.remove('hidden');
        } else {
            actions.classList.add('hidden');
        }

        modal.classList.remove('hidden');
    }

    function closeModal() {
        const modal = document.getElementById('qris-modal');
        modal.classList.add('hidden');
    }
</script>
@endsection