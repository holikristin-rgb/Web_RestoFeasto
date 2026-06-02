@extends('layouts.app')

@section('content')
<section class="container mx-auto px-6 py-12 max-w-4xl">
    <div class="text-center mb-8">
        <h1 class="text-3xl md:text-4xl font-serif font-bold text-[#4A2C2A] uppercase tracking-widest">Rincian Reservasi</h1>
        <p class="text-gray-500 mt-2">Lengkapi data reservasi tempat dan konfirmasi menu pilihan Anda</p>
        <div class="h-1 w-20 bg-orange-600 mx-auto mt-4 rounded-full"></div>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-xl shadow-sm mb-6">
            <p class="font-bold mb-1">Terjadi kesalahan input:</p>
            <ul class="list-disc list-inside text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Form inputs -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-gray-100">
                <form action="{{ route('reservasi.store') }}" method="POST" id="booking-form" class="space-y-6">
                    @csrf
                    
                    <h3 class="text-lg font-bold text-[#4A2C2A] border-b border-gray-100 pb-3 uppercase tracking-wider">
                        1. Informasi Kunjungan
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Kunjungan</label>
                            <input type="date" name="tanggal_reservasi" id="tanggal_reservasi" required
                                   min="{{ date('Y-m-d') }}" value="{{ old('tanggal_reservasi', date('Y-m-d')) }}"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-orange-600 focus:border-orange-600 outline-none transition">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah Tamu (Orang)</label>
                            <input type="number" name="jumlah_orang" id="jumlah_orang" min="1" required
                                   value="{{ old('jumlah_orang', '2') }}"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-orange-600 focus:border-orange-600 outline-none transition">
                        </div>
                    </div>

                    <!-- Dynamic Tables Selection Container -->
                    <div class="mt-8">
                        <h3 class="text-lg font-bold text-[#4A2C2A] border-b border-gray-100 pb-3 mb-4 uppercase tracking-wider">
                            2. Pilih Meja Makan
                        </h3>
                        <p class="text-xs text-gray-400 mb-4">Meja di bawah difilter otomatis berdasarkan kapasitas tamu.</p>
                        
                        <div id="tables-loader" class="hidden py-4 text-center text-xs text-gray-500 font-medium">
                            <span class="animate-pulse">Memuat meja yang tersedia...</span>
                        </div>

                        <div id="tables-container" class="grid grid-cols-3 sm:grid-cols-4 gap-4">
                            <!-- Injected dynamically by JS -->
                        </div>

                        <div id="no-tables-message" class="hidden text-center py-6 text-sm text-red-500 bg-red-50 border border-red-100 rounded-2xl font-semibold">
                            Tidak ada meja makan yang sesuai dengan jumlah tamu tersebut. Silakan atur kembali jumlah tamu Anda.
                        </div>
                    </div>

                    <div class="pt-6 border-t border-gray-100">
                        <button type="submit" id="submit-btn" disabled
                                class="w-full bg-[#4A2C2A] text-white py-4 rounded-2xl font-bold uppercase tracking-widest shadow-md hover:bg-orange-600 transition disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed">
                            Buat Reservasi Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right: Order summary list -->
        <div class="space-y-6">
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 sticky top-24">
                <h3 class="text-lg font-serif font-bold text-[#4A2C2A] border-b border-gray-100 pb-3 mb-4 uppercase tracking-wider">
                    Pesanan Menu
                </h3>

                <ul class="divide-y divide-gray-100 -my-4">
                    @php $subtotal = 0; @endphp
                    @foreach($cart as $item)
                        @php $subtotal += $item['harga'] * $item['qty']; @endphp
                        <li class="flex justify-between py-4 text-sm">
                            <div class="flex-grow pr-4">
                                <h4 class="font-bold text-gray-800">{{ $item['nama_menu'] }}</h4>
                                <span class="text-xs text-gray-400">Qty: {{ $item['qty'] }} x Rp {{ number_format($item['harga'], 0, ',', '.') }}</span>
                            </div>
                            <span class="font-bold text-orange-600 whitespace-nowrap">Rp {{ number_format($item['harga'] * $item['qty'], 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="border-t border-gray-100 pt-4 mt-6">
                    <div class="flex justify-between text-base font-bold text-[#4A2C2A] mb-2">
                        <span>Total Bayar</span>
                        <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="bg-orange-50 border border-orange-100 text-orange-800 text-[10px] p-3 rounded-xl font-medium leading-relaxed">
                        ⚠️ Pemesanan dibatalkan otomatis jika bukti transfer pembayaran tidak diunggah dalam waktu 24 jam.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dynamic table loading script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const qtyInput = document.getElementById('jumlah_orang');
        const dateInput = document.getElementById('tanggal_reservasi');
        
        // Initial load
        fetchAvailableTables();

        // Listen for changes
        qtyInput.addEventListener('input', fetchAvailableTables);
        dateInput.addEventListener('change', fetchAvailableTables);
    });

    function fetchAvailableTables() {
        const qty = document.getElementById('jumlah_orang').value;
        const date = document.getElementById('tanggal_reservasi').value;
        const container = document.getElementById('tables-container');
        const loader = document.getElementById('tables-loader');
        const noTablesMsg = document.getElementById('no-tables-message');
        const submitBtn = document.getElementById('submit-btn');

        if (!qty || qty <= 0) return;

        loader.classList.remove('hidden');
        container.classList.add('hidden');
        noTablesMsg.classList.add('hidden');
        submitBtn.disabled = true;

        fetch(`{{ route('tables.available') }}?jumlah_orang=${qty}&tanggal_reservasi=${date}`)
            .then(res => res.json())
            .then(tables => {
                loader.classList.add('hidden');
                container.innerHTML = '';
                
                if (tables.length === 0) {
                    noTablesMsg.classList.remove('hidden');
                    return;
                }

                container.classList.remove('hidden');
                
                let firstAvailableChecked = false;
                let hasAvailableTables = false;

                tables.forEach((table, index) => {
                    const label = document.createElement('label');
                    
                    if (table.is_booked) {
                        label.className = 'flex flex-col items-center cursor-not-allowed opacity-60';
                        label.innerHTML = `
                            <input type="radio" disabled name="meja_id" value="${table.meja_id}" class="hidden">
                            <div class="w-full py-4 px-2 text-center bg-gray-100 border border-gray-200 rounded-2xl text-gray-400 shadow-sm flex flex-col items-center">
                                <span class="font-bold text-sm tracking-tight">${table.nomor_meja}</span>
                                <span class="text-[9px] uppercase tracking-wider mt-1 text-red-500 font-bold">Sudah Dipesan</span>
                            </div>
                        `;
                    } else {
                        hasAvailableTables = true;
                        label.className = 'flex flex-col items-center cursor-pointer group';
                        const checkedAttribute = !firstAvailableChecked ? 'checked' : '';
                        if (!firstAvailableChecked) firstAvailableChecked = true;
                        
                        label.innerHTML = `
                            <input type="radio" name="meja_id" value="${table.meja_id}" required class="hidden peer" ${checkedAttribute}>
                            <div class="w-full py-4 px-2 text-center bg-gray-50 border border-gray-200 rounded-2xl transition hover:bg-orange-50 group-hover:border-orange-200 peer-checked:bg-orange-600 peer-checked:text-white peer-checked:border-orange-600 shadow-sm flex flex-col items-center">
                                <span class="font-bold text-sm tracking-tight">${table.nomor_meja}</span>
                                <span class="text-[9px] uppercase tracking-wider mt-1 opacity-70">Kap. ${table.kapasitas}</span>
                            </div>
                        `;
                    }
                    container.appendChild(label);
                });

                submitBtn.disabled = !hasAvailableTables;
            })
            .catch(err => {
                console.error(err);
                loader.classList.add('hidden');
            });
    }
</script>
@endsection