@extends('layouts.app')

@section('content')
<section class="container mx-auto px-6 py-12 max-w-4xl">
    <div class="text-center mb-10">
        <h1 class="text-3xl md:text-4xl font-serif font-bold text-[#4A2C2A] uppercase tracking-widest">Riwayat Reservasi & Pembayaran</h1>
        <p class="text-gray-500 mt-2">Pantau status reservasi meja, upload bukti pembayaran QRIS, dan berikan masukan Anda</p>
        <div class="h-1 w-20 bg-orange-600 mx-auto mt-4 rounded-full"></div>
    </div>

    <div class="space-y-8">
        @forelse($reservations as $res)
            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-gray-100 flex flex-col md:flex-row gap-8 justify-between relative overflow-hidden">
                <!-- Status bar decoration based on status -->
                @php
                    $statusColors = [
                        'pending' => 'bg-yellow-500',
                        'confirmed' => 'bg-green-500',
                        'completed' => 'bg-blue-500',
                        'cancelled' => 'bg-red-500'
                    ];
                    $barColor = $statusColors[$res->status] ?? 'bg-gray-500';
                @endphp
                <div class="absolute top-0 left-0 right-0 h-1.5 {{ $barColor }}"></div>

                <!-- Left: Booking info & orders -->
                <div class="flex-grow space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-orange-600 bg-orange-50 px-3 py-1 rounded-full">
                            ID: #RES-{{ $res->reservasi_id }}
                        </span>
                        <span class="text-xs font-bold uppercase tracking-widest text-white px-3 py-1 rounded-full {{ $barColor }}">
                            {{ $res->status }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-2 gap-x-4 text-xs font-semibold text-gray-700">
                        <div>Tanggal: <span class="text-gray-900 font-bold">{{ date('d M Y', strtotime($res->tanggal_reservasi)) }}</span></div>
                        <div>Meja: <span class="text-gray-900 font-bold">{{ $res->meja->nomor_meja ?? '-' }}</span></div>
                        <div>Jumlah Tamu: <span class="text-gray-900 font-bold">{{ $res->jumlah_orang }} Orang</span></div>
                    </div>

                    <!-- Ordered menus summary -->
                    <div class="border-t border-gray-50 pt-3 mt-3">
                        <p class="text-xs font-bold text-[#4A2C2A] mb-2 uppercase tracking-wide">Pesanan:</p>
                        <div class="space-y-1 text-xs text-gray-500">
                            @foreach($res->order_items as $item)
                                <div class="flex justify-between max-w-md">
                                    <span>{{ $item->menu->nama_menu ?? 'Menu' }} (x{{ $item->jumlah }})</span>
                                    <span class="font-bold text-gray-700">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Right: Action area (Payment details, receipt upload, or feedback form) -->
                <div class="md:w-80 flex-shrink-0 border-t md:border-t-0 md:border-l border-gray-100 pt-6 md:pt-0 md:pl-6 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center text-sm font-bold text-[#4A2C2A] mb-4">
                            <span>Total Harga:</span>
                            <span class="text-lg text-orange-600">Rp {{ number_format($res->total_harga, 0, ',', '.') }}</span>
                        </div>

                        <!-- 1. Payment not submitted yet -->
                        @if(!$res->pembayaran || $res->pembayaran->status === 'ditolak')
                            @if($res->status === 'cancelled')
                                <div class="bg-red-50 text-red-700 text-xs p-3 rounded-2xl font-medium">
                                    Reservasi dibatalkan.
                                </div>
                            @else
                                <div class="space-y-3">
                                    @if($res->pembayaran && $res->pembayaran->status === 'ditolak')
                                        <div class="bg-red-50 text-red-700 text-[10px] p-2.5 rounded-xl font-bold mb-2">
                                            ❌ Bukti transfer ditolak kasir. Silakan upload ulang.
                                        </div>
                                    @endif
                                    
                                    <!-- Payment Options Toggle -->
                                    <div class="flex border border-gray-200 rounded-xl overflow-hidden text-xs font-bold mb-3">
                                        <button onclick="togglePayOption({{ $res->reservasi_id }}, 'qris')" id="btn-qris-{{ $res->reservasi_id }}" class="flex-1 py-2 text-center bg-[#4A2C2A] text-white">QRIS (Transfer)</button>
                                        <button onclick="togglePayOption({{ $res->reservasi_id }}, 'tunai')" id="btn-tunai-{{ $res->reservasi_id }}" class="flex-1 py-2 text-center bg-gray-50 text-gray-500">Tunai (Di Resto)</button>
                                    </div>

                                    <!-- Option QRIS: File upload -->
                                    <div id="qris-form-{{ $res->reservasi_id }}" class="space-y-3">
                                        <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-200 text-center flex flex-col items-center">
                                            <p class="text-[9px] uppercase tracking-wider text-gray-500 font-bold mb-1">Scan QRIS RestoFeasto</p>
                                            <!-- Scan QRIS RestoFeasto -->
                                            @php
                                                $qrisImageSetting = \App\Models\Setting::getValue('qris_image', 'qris/default_qris.jpg');
                                            @endphp
                                            <img src="{{ route('storage.file', ['path' => $qrisImageSetting]) }}" alt="QRIS" class="w-48 h-auto shadow-sm border p-1 bg-white rounded-lg">
                                            <p class="text-[9px] text-gray-400 mt-1 font-semibold">Total: Rp {{ number_format($res->total_harga, 0, ',', '.') }}</p>
                                        </div>

                                        <form action="{{ route('pembayaran.store', $res->reservasi_id) }}" method="POST" enctype="multipart/form-data" class="space-y-2">
                                            @csrf
                                            <input type="hidden" name="metode" value="qris">
                                            <label class="block">
                                                <span class="sr-only">Bukti Pembayaran</span>
                                                <input type="file" name="bukti_bayar" required accept="image/*"
                                                       class="block w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-bold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer">
                                            </label>
                                            <button type="submit" class="w-full bg-[#4A2C2A] hover:bg-black text-white text-[11px] font-bold py-2 rounded-xl uppercase tracking-wider transition">
                                                Kirim Bukti Bayar
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Option Tunai: Direct confirm -->
                                    <div id="tunai-form-{{ $res->reservasi_id }}" class="hidden">
                                        <form action="{{ route('pembayaran.store', $res->reservasi_id) }}" method="POST" class="space-y-2">
                                            @csrf
                                            <input type="hidden" name="metode" value="tunai">
                                            <div class="bg-orange-50 text-[#4A2C2A] text-[10px] p-3 rounded-xl leading-relaxed font-semibold mb-3">
                                                Anda memilih pembayaran tunai. Pembayaran akan dilakukan di kasir saat kedatangan.
                                            </div>
                                            <button type="submit" class="w-full bg-[#4A2C2A] hover:bg-black text-white text-[11px] font-bold py-2 rounded-xl uppercase tracking-wider transition">
                                                Konfirmasi Tunai
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif

                        <!-- 2. Payment submitted and pending verification -->
                        @elseif($res->pembayaran->status === 'pending')
                            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-2xl text-xs space-y-1">
                                <p class="font-bold flex items-center gap-1">🕒 Menunggu Konfirmasi</p>
                                <p class="text-[10px] font-medium leading-relaxed">
                                    Metode: <b class="uppercase">{{ $res->pembayaran->metode }}</b><br>
                                    Bukti bayar Anda sedang diverifikasi oleh kasir. Mohon tunggu beberapa saat.
                                </p>
                            </div>

                        <!-- 3. Payment approved -->
                        @elseif($res->pembayaran->status === 'diterima')
                            @if($res->status === 'completed')
                                <!-- Display review / feedback widget -->
                                @if(!$res->feedback)
                                    <div class="bg-gray-50 border border-gray-200 p-4 rounded-2xl space-y-3">
                                        <p class="text-xs font-bold text-[#4A2C2A] uppercase tracking-wide">Beri Ulasan:</p>
                                        <form action="{{ route('feedback.store', $res->reservasi_id) }}" method="POST" class="space-y-2">
                                            @csrf
                                            <!-- Stars input -->
                                            <div class="flex items-center gap-1 text-orange-500">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <label class="cursor-pointer">
                                                        <input type="radio" name="rating" value="{{ $i }}" required class="hidden peer">
                                                        <svg onclick="highlightStars({{ $res->reservasi_id }}, {{ $i }})" 
                                                             id="star-{{ $res->reservasi_id }}-{{ $i }}" 
                                                             class="h-6 w-6 text-gray-300 hover:text-orange-500 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                    </label>
                                                @endfor
                                            </div>
                                            <textarea name="komentar" placeholder="Tulis masukan tentang rasa makanan dan kenyamanan..." required
                                                      class="w-full p-2.5 border border-gray-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-orange-600 transition" rows="2"></textarea>
                                            <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white text-[10px] font-bold py-2 rounded-xl uppercase tracking-wider transition">
                                                Kirim Ulasan
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="bg-blue-50 border border-blue-100 p-4 rounded-2xl text-xs space-y-1 text-blue-900">
                                        <p class="font-bold">⭐ Nilai Anda: {{ $res->feedback->rating }}/5</p>
                                        <p class="text-[10px] italic leading-relaxed text-blue-800">"{{ $res->feedback->komentar }}"</p>
                                    </div>
                                @endif
                            @else
                                <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-2xl text-xs space-y-1">
                                    <p class="font-bold flex items-center gap-1">✅ Pembayaran Terkonfirmasi</p>
                                    <p class="text-[10px] font-medium leading-relaxed">
                                        Metode: <b class="uppercase">{{ $res->pembayaran->metode }}</b><br>
                                        Silakan datang ke resto pada hari H. Tunjukkan ID Reservasi ini kepada pelayan.
                                    </p>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl p-12 text-center text-gray-500 shadow-sm border border-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="font-semibold text-gray-600">Belum ada riwayat pemesanan</p>
                <p class="text-xs text-gray-400 mt-1">Lakukan pemesanan menu dan reservasi meja untuk memulainya.</p>
                <a href="{{ route('beranda') }}" class="mt-6 inline-block bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold uppercase tracking-wider px-6 py-3 rounded-full shadow transition">Lihat Menu Nusantara</a>
            </div>
        @endforelse
    </div>
</section>

<!-- Payment options toggle & stars scripts -->
<script>
    function togglePayOption(resId, option) {
        const qrisForm = document.getElementById(`qris-form-${resId}`);
        const tunaiForm = document.getElementById(`tunai-form-${resId}`);
        const btnQris = document.getElementById(`btn-qris-${resId}`);
        const btnTunai = document.getElementById(`btn-tunai-${resId}`);

        if (option === 'qris') {
            qrisForm.classList.remove('hidden');
            tunaiForm.classList.add('hidden');
            btnQris.className = 'flex-1 py-2 text-center bg-[#4A2C2A] text-white';
            btnTunai.className = 'flex-1 py-2 text-center bg-gray-50 text-gray-500';
        } else {
            qrisForm.classList.add('hidden');
            tunaiForm.classList.remove('hidden');
            btnQris.className = 'flex-1 py-2 text-center bg-gray-50 text-gray-500';
            btnTunai.className = 'flex-1 py-2 text-center bg-[#4A2C2A] text-white';
        }
    }

    function highlightStars(resId, rating) {
        for (let i = 1; i <= 5; i++) {
            const star = document.getElementById(`star-${resId}-${i}`);
            if (i <= rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-orange-500');
            } else {
                star.classList.remove('text-orange-500');
                star.classList.add('text-gray-300');
            }
        }
    }
</script>
@endsection