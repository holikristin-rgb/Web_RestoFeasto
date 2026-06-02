@extends('layouts.app')

@section('content')
<section class="container mx-auto px-6 mt-8">
    <div class="relative w-full h-[400px] rounded-3xl overflow-hidden shadow-2xl flex items-center justify-center border-b-8 border-orange-600">
        <div class="absolute inset-0 bg-[#4A2C2A]"></div>
        <div class="absolute inset-0 opacity-15" style="background-image: url('https://www.transparenttextures.com/patterns/batik-fret.png');"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-[#4A2C2A]/40 to-black/20"></div>

        <div class="relative z-10 text-center px-4 max-w-2xl">
            <span class="inline-block text-[#D4A373] font-bold tracking-[0.3em] uppercase text-xs mb-3 border-b border-[#D4A373] pb-2">
                Warisan Kuliner Autentik
            </span>

            <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif font-bold text-white leading-tight mb-4">
                Cita Rasa <span class="text-orange-500 italic">Nusantara</span>
            </h1>

            <p class="text-gray-300 text-sm md:text-base mb-8 font-light tracking-wide leading-relaxed">
                Nikmati hidangan legendaris dari resep leluhur yang disajikan segar setiap hari. Pesan meja Anda dan rancang menu istimewa langsung di ujung jari.
            </p>

            @if(!Auth::check())
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ route('register') }}" class="bg-orange-600 hover:bg-orange-700 text-white px-8 py-3.5 rounded-full font-bold transition transform hover:-translate-y-1 shadow-lg text-sm uppercase tracking-wider">
                        Daftar Akun
                    </a>
                    <a href="{{ route('login') }}" class="bg-white/10 hover:bg-white/20 text-white border border-white/20 px-8 py-3.5 rounded-full font-bold transition transform hover:-translate-y-1 text-sm uppercase tracking-wider">
                        Lihat Menu
                    </a>
                </div>
            @else
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="#menu-section" class="bg-orange-600 hover:bg-orange-700 text-white px-10 py-4 rounded-full font-bold transition transform hover:-translate-y-1 shadow-lg text-sm uppercase tracking-wider">
                        Lihat Menu Hari Ini
                    </a>
                </div>
            @endif
        </div>
    </div>
</section>

{{-- Section menu ini hanya akan muncul jika user sudah login --}}
@auth
<section id="menu-section" class="container mx-auto px-6 py-16 scroll-mt-20">
    <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-serif font-bold text-[#4A2C2A] uppercase tracking-widest">Menu Nusantara</h2>
        <p class="text-gray-500 mt-2 max-w-md mx-auto">Kami menyajikan makanan berat dan minuman khas nusantara dengan bahan pilihan berkualitas.</p>
        <div class="h-1 w-20 bg-orange-600 mx-auto mt-4 rounded-full"></div>
    </div>

    <div class="flex flex-wrap justify-center gap-3 mb-12">
        <a href="{{ Auth::check() ? route('beranda') : route('index') }}#menu-section" 
           class="px-6 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider transition duration-300 shadow-sm {{ !$selected_category ? 'bg-orange-600 text-white' : 'bg-white text-gray-500 hover:bg-orange-50 hover:text-orange-600 border border-gray-100' }}">
           Semua Menu
        </a>
        @foreach($categories as $cat)
            <a href="{{ (Auth::check() ? route('beranda') : route('index')) }}?kategori={{ $cat->id_kategori }}#menu-section" 
               class="px-6 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider transition duration-300 shadow-sm {{ $selected_category == $cat->id_kategori ? 'bg-orange-600 text-white' : 'bg-white text-gray-500 hover:bg-orange-50 hover:text-orange-600 border border-gray-100' }}">
                {{ $cat->nama_kategori }}
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($menus as $menu)
            @php
                if ($menu->gambar) {
                    $imageUrl = route('storage.file', ['path' => $menu->gambar]);
                } else {
                    $imageUrl = asset('images/default-menu.jpg'); 
                }
            @endphp

            <div class="group bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1.5 transition duration-500 flex flex-col h-full">
                <div class="h-52 w-full bg-gray-100 relative overflow-hidden">
                    <img src="{{ $imageUrl }}" alt="{{ $menu->nama_menu }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-60"></div>
                    <div class="absolute top-4 right-4 bg-orange-600 text-white px-3.5 py-1 rounded-full text-[10px] font-bold shadow-md uppercase tracking-wider">
                        {{ $menu->kategori->nama_kategori ?? 'Menu' }}
                    </div>
                </div>

                <div class="p-6 flex flex-col flex-grow">
                    <h3 class="text-xl font-bold text-[#4A2C2A] mb-2 group-hover:text-orange-600 transition">{{ $menu->nama_menu }}</h3>
                    <p class="text-gray-500 text-xs line-clamp-3 mb-6 font-light leading-relaxed flex-grow">{{ $menu->deskripsi }}</p>
                    
                    <div class="flex items-center justify-between border-t border-gray-50 pt-4 mt-auto">
                        <div class="flex flex-col">
                            <span class="text-xs text-gray-400 font-semibold">Harga</span>
                            <span class="text-xl font-black text-orange-600">Rp {{ number_format($menu->harga, 0, ',', '.') }}</span>
                        </div>

                        <div class="text-right">
                            @if(Auth::check() && Auth::user()->role === 'pelanggan')
                                @if($menu->stok > 0)
                                    <button onclick="addToCart({{ $menu->menu_id }}, '{{ addslashes($menu->nama_menu) }}', {{ $menu->harga }}, {{ $menu->stok }})" 
                                            class="bg-[#4A2C2A] hover:bg-orange-600 text-white p-3 rounded-2xl transition duration-300 shadow-md flex items-center gap-2 text-xs font-bold uppercase tracking-wider">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Pesan
                                    </button>
                                @else
                                    <span class="inline-block bg-red-100 text-red-700 px-3 py-1.5 rounded-full text-xs font-bold uppercase">Habis</span>
                                @endif
                            @elseif(Auth::check())
                                <span class="text-xs font-bold text-gray-400 bg-gray-100 px-3 py-1.5 rounded-xl uppercase">Staff Mode</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mt-3 flex items-center justify-between text-[11px] text-gray-400 border-t border-dashed border-gray-100 pt-2">
                        <span>Tersedia: <b>{{ $menu->stok }} porsi</b></span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center text-gray-500 font-medium">
                Belum ada menu yang terdaftar di kategori ini.
            </div>
        @endforelse
    </div>
</section>
@endauth

@if(count($feedbacks) > 0)
<section class="bg-gray-50/50 py-16 border-t border-b border-gray-100">
    <div class="container mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-serif font-bold text-[#4A2C2A] uppercase tracking-widest">Ulasan Pelanggan</h2>
            <p class="text-gray-500 mt-2 max-w-md mx-auto">Apa kata mereka yang telah merasakan pengalaman kuliner di RestoFeasto</p>
            <div class="h-1 w-20 bg-orange-600 mx-auto mt-4 rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($feedbacks as $fb)
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm flex flex-col justify-between relative group hover:shadow-lg transition duration-300">
                    <span class="absolute top-6 right-8 text-6xl text-orange-100 font-serif Bird-none leading-none select-none group-hover:text-orange-200 transition">”</span>
                    
                    <div class="space-y-4">
                        <div class="flex text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ $i <= $fb->rating ? 'fill-current text-yellow-400' : 'text-gray-200' }}" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>

                        <p class="text-gray-600 text-xs italic leading-relaxed relative z-10">
                            "{{ $fb->komentar }}"
                        </p>
                    </div>

                    <div class="flex items-center gap-3 mt-6 pt-4 border-t border-gray-50">
                        <div class="w-10 h-10 bg-orange-100 text-orange-700 font-bold rounded-full flex items-center justify-center text-sm uppercase">
                            {{ substr($fb->user->nama ?? 'P', 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-xs text-[#4A2C2A]">{{ $fb->user->nama ?? 'Pelanggan' }}</h4>
                            <span class="text-[10px] text-gray-400 font-medium">{{ date('d M Y', strtotime($fb->tanggal)) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<style>
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;  
        overflow: hidden;
    }
</style>
@endsection