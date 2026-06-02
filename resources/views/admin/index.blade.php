@extends('layouts.app')

@section('content')
<section class="container mx-auto px-6 py-12">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8 border-b border-gray-100 pb-6">
        <div>
            <h1 class="text-3xl font-bold text-[#4A2C2A]">Panel Admin RestoFeasto</h1>
            <p class="text-gray-500">Kelola menu hidangan, kategori masakan, meja makan, dan data staf restoran</p>
        </div>
        <div class="text-xs font-bold text-gray-400 uppercase tracking-widest bg-gray-100 px-4 py-2 rounded-full">
            Admin Mode
        </div>
    </div>

    <!-- Error validation banner -->
    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-2xl shadow-sm mb-6 text-xs font-semibold">
            <p class="font-bold mb-1">Terjadi kesalahan input:</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tab Navigation -->
    <div class="flex border-b border-gray-200 mb-8 font-bold text-sm">
        <button onclick="switchTab('menu')" id="tab-menu" class="px-6 py-3 text-orange-600 border-b-2 border-orange-600 transition outline-none">Kelola Menu</button>
        <button onclick="switchTab('category')" id="tab-category" class="px-6 py-3 text-gray-500 hover:text-orange-600 transition outline-none">Kategori Menu</button>
        <button onclick="switchTab('table')" id="tab-table" class="px-6 py-3 text-gray-500 hover:text-orange-600 transition outline-none">Meja Makan</button>
        <button onclick="switchTab('staff')" id="tab-staff" class="px-6 py-3 text-gray-500 hover:text-orange-600 transition outline-none">Daftar Staf (Staff)</button>
        <button onclick="switchTab('feedback')" id="tab-feedback" class="px-6 py-3 text-gray-500 hover:text-orange-600 transition outline-none">Ulasan Pelanggan</button>
        <button onclick="switchTab('settings')" id="tab-settings" class="px-6 py-3 text-gray-500 hover:text-orange-600 transition outline-none">Pengaturan Resto</button>
    </div>

    <!-- ================== TAB 1: KELOLA MENU ================== -->
    <div id="section-menu" class="space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Table of menus (left) -->
            <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col justify-between">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
                    <h2 class="font-bold text-xs uppercase text-[#4A2C2A] tracking-wider">Daftar Menu Hidangan</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50 border-b border-gray-100">
                            <tr class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                <th class="px-6 py-3">Nama Menu</th>
                                <th class="px-6 py-3">Kategori</th>
                                <th class="px-6 py-3">Harga</th>
                                <th class="px-6 py-3">Stok</th>
                                <th class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs font-semibold text-gray-700">
                            @forelse($menus as $menu)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-[#4A2C2A] flex items-center gap-2">
                                            @if($menu->gambar)
                                                <img src="{{ route('storage.file', ['path' => $menu->gambar]) }}" alt="{{ $menu->nama_menu }}" class="w-8 h-8 rounded-lg object-cover border border-gray-100">
                                            @endif
                                            <span>{{ $menu->nama_menu }}</span>
                                        </div>
                                        <div class="text-[10px] text-gray-400 font-normal line-clamp-1 mt-0.5">{{ $menu->deskripsi }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="bg-orange-50 text-orange-700 border border-orange-100 px-2.5 py-0.5 rounded text-[10px] font-bold">
                                            {{ $menu->kategori->nama_kategori ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-orange-600 font-bold">Rp {{ number_format($menu->harga, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 font-bold">{{ $menu->stok }} Porsi</td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center gap-2">
                                            <button onclick="openMenuEditModal('{{ $menu->menu_id }}', '{{ addslashes($menu->nama_menu) }}', '{{ $menu->id_kategori }}', '{{ $menu->harga }}', '{{ $menu->stok }}', '{{ addslashes($menu->deskripsi) }}')"
                                                    class="text-blue-600 hover:text-blue-800 font-bold bg-blue-50 px-2.5 py-1.5 rounded-lg border border-blue-100">Edit</button>
                                            
                                            <form action="{{ route('admin.menu.destroy', $menu->menu_id) }}" method="POST" onsubmit="return confirm('Hapus menu ini?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 font-bold bg-red-50 px-2.5 py-1.5 rounded-lg border border-red-100">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-gray-500 font-medium">Belum ada menu terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Form: Add Menu (right) -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4 h-fit">
                <h3 class="text-sm font-bold text-[#4A2C2A] uppercase tracking-wider border-b border-gray-100 pb-3">Tambah Menu Baru</h3>
                <form action="{{ route('admin.menu.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold text-gray-700">
                    @csrf
                    <div>
                        <label class="block mb-1.5">Nama Menu</label>
                        <input type="text" name="nama_menu" required placeholder="Contoh: Nasi Goreng Kampung"
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                    </div>
                    <div>
                        <label class="block mb-1.5">Kategori</label>
                        <select name="id_kategori" required class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id_kategori }}">{{ $cat->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1.5">Harga (Rp)</label>
                            <input type="number" name="harga" min="0" required placeholder="25000"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                        </div>
                        <div>
                            <label class="block mb-1.5">Stok</label>
                            <input type="number" name="stok" min="0" required placeholder="50"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1.5">Gambar Hidangan (Opsional)</label>
                        <input type="file" name="gambar" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition text-xs file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-bold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer">
                    </div>
                    <div>
                        <label class="block mb-1.5">Deskripsi</label>
                        <textarea name="deskripsi" required placeholder="Tuliskan racikan bumbu khas menu..." rows="3"
                                  class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-[#4A2C2A] hover:bg-orange-600 text-white font-bold py-3 rounded-xl uppercase tracking-wider transition shadow">
                        Simpan Menu
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ================== TAB 2: KELOLA KATEGORI ================== -->
    <div id="section-category" class="space-y-8 hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col justify-between">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
                    <h2 class="font-bold text-xs uppercase text-[#4A2C2A] tracking-wider">Daftar Kategori Masakan</h2>
                </div>
                <table class="w-full text-left text-xs font-semibold text-gray-700">
                    <thead class="bg-gray-50/50 border-b border-gray-100">
                        <tr class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            <th class="px-6 py-3">ID Kategori</th>
                            <th class="px-6 py-3">Nama Kategori</th>
                            <th class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($categories as $cat)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4 font-bold text-orange-600">#{{ $cat->id_kategori }}</td>
                                <td class="px-6 py-4 text-gray-900 font-bold">{{ $cat->nama_kategori }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button onclick="openCategoryEditModal('{{ $cat->id_kategori }}', '{{ addslashes($cat->nama_kategori) }}')"
                                                class="text-blue-600 hover:text-blue-800 font-bold bg-blue-50 px-2.5 py-1.5 rounded-lg border border-blue-100">Edit</button>
                                        
                                        <form action="{{ route('admin.category.destroy', $cat->id_kategori) }}" method="POST" onsubmit="return confirm('Hapus kategori ini? Semua menu dengan kategori ini juga akan terhapus.')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 font-bold bg-red-50 px-2.5 py-1.5 rounded-lg border border-red-100">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-12 text-center text-gray-500 font-medium">Belum ada kategori terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4 h-fit">
                <h3 class="text-sm font-bold text-[#4A2C2A] uppercase tracking-wider border-b border-gray-100 pb-3">Tambah Kategori Baru</h3>
                <form action="{{ route('admin.category.store') }}" method="POST" class="space-y-4 text-xs font-semibold text-gray-700">
                    @csrf
                    <div>
                        <label class="block mb-1.5">Nama Kategori</label>
                        <input type="text" name="nama_kategori" required placeholder="Contoh: Makanan Berat"
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                    </div>
                    <button type="submit" class="w-full bg-[#4A2C2A] hover:bg-orange-600 text-white font-bold py-3 rounded-xl uppercase tracking-wider transition shadow">
                        Simpan Kategori
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ================== TAB 3: KELOLA MEJA ================== -->
    <div id="section-table" class="space-y-8 hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col justify-between">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
                    <h2 class="font-bold text-xs uppercase text-[#4A2C2A] tracking-wider">Daftar Meja Restoran</h2>
                </div>
                <table class="w-full text-left text-xs font-semibold text-gray-700">
                    <thead class="bg-gray-50/50 border-b border-gray-100">
                        <tr class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            <th class="px-6 py-3">Nomor Meja</th>
                            <th class="px-6 py-3">Kapasitas Maks</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($tables as $table)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4 font-bold text-[#4A2C2A]">{{ $table->nomor_meja }}</td>
                                <td class="px-6 py-4">{{ $table->kapasitas }} Orang</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $table->status === 'tersedia' ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-red-50 text-red-700 border border-red-100' }}">
                                        {{ $table->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button onclick="openTableEditModal('{{ $table->meja_id }}', '{{ $table->nomor_meja }}', '{{ $table->kapasitas }}', '{{ $table->status }}')"
                                                class="text-blue-600 hover:text-blue-800 font-bold bg-blue-50 px-2.5 py-1.5 rounded-lg border border-blue-100">Edit</button>
                                        
                                        <form action="{{ route('admin.table.destroy', $table->meja_id) }}" method="POST" onsubmit="return confirm('Hapus meja ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 font-bold bg-red-50 px-2.5 py-1.5 rounded-lg border border-red-100">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center text-gray-500 font-medium">Belum ada meja terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4 h-fit">
                <h3 class="text-sm font-bold text-[#4A2C2A] uppercase tracking-wider border-b border-gray-100 pb-3">Tambah Meja Makan Baru</h3>
                <form action="{{ route('admin.table.store') }}" method="POST" class="space-y-4 text-xs font-semibold text-gray-700">
                    @csrf
                    <div>
                        <label class="block mb-1.5">Nomor Meja</label>
                        <input type="text" name="nomor_meja" required placeholder="Contoh: M12"
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                    </div>
                    <div>
                        <label class="block mb-1.5">Kapasitas (Orang)</label>
                        <input type="number" name="kapasitas" min="1" required placeholder="4"
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                    </div>
                    <div>
                        <label class="block mb-1.5">Status</label>
                        <select name="status" required class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                            <option value="tersedia">Tersedia</option>
                            <option value="dipesan">Dipesan</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-[#4A2C2A] hover:bg-orange-600 text-white font-bold py-3 rounded-xl uppercase tracking-wider transition shadow">
                        Simpan Meja
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ================== TAB 4: REGSITRASI STAF ================== -->
    <div id="section-staff" class="space-y-8 hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col justify-between">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
                    <h2 class="font-bold text-xs uppercase text-[#4A2C2A] tracking-wider">Daftar Staf Restoran</h2>
                </div>
                <table class="w-full text-left text-xs font-semibold text-gray-700">
                    <thead class="bg-gray-50/50 border-b border-gray-100">
                        <tr class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            <th class="px-6 py-3">Nama Lengkap</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">No. HP</th>
                            <th class="px-6 py-3">Peran (Role)</th>
                            <th class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($staff as $st)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4 text-[#4A2C2A] font-bold">{{ $st->nama }}</td>
                                <td class="px-6 py-4">{{ $st->email }}</td>
                                <td class="px-6 py-4">{{ $st->no_hp ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $st->role === 'admin' ? 'bg-[#FFF5EE] text-[#FF6B00] border border-[#FFE4D3]' : 'bg-blue-50 text-blue-700 border border-blue-100' }}">
                                        {{ $st->role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button onclick="openStaffEditModal('{{ $st->user_id }}', '{{ addslashes($st->nama) }}', '{{ $st->email }}', '{{ $st->no_hp }}', '{{ $st->role }}')"
                                                class="text-blue-600 hover:text-blue-800 font-bold bg-blue-50 px-2.5 py-1.5 rounded-lg border border-blue-100">Edit</button>
                                        
                                        @if($st->email !== 'admin@restofeasto.com')
                                            <form action="{{ route('admin.staff.destroy', $st->user_id) }}" method="POST" onsubmit="return confirm('Hapus staf ini?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 font-bold bg-red-50 px-2.5 py-1.5 rounded-lg border border-red-100">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-gray-500 font-medium">Belum ada staf terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4 h-fit">
                <h3 class="text-sm font-bold text-[#4A2C2A] uppercase tracking-wider border-b border-gray-100 pb-3">Registrasi Staf Baru</h3>
                <form action="{{ route('admin.staff.store') }}" method="POST" class="space-y-4 text-xs font-semibold text-gray-700">
                    @csrf
                    <div>
                        <label class="block mb-1.5">Nama Lengkap</label>
                        <input type="text" name="nama" required placeholder="Masukkan nama lengkap staf"
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                    </div>
                    <div>
                        <label class="block mb-1.5">Email</label>
                        <input type="email" name="email" required placeholder="staf@restofeasto.com"
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                    </div>
                    <div>
                        <label class="block mb-1.5">No. HP / Telepon</label>
                        <input type="text" name="no_hp" required placeholder="08xxxxxxxxxx"
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                    </div>
                    <div>
                        <label class="block mb-1.5">Password</label>
                        <input type="password" name="password" minlength="8" required placeholder="Buat password staf minimal 8 karakter"
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                    </div>
                    <div>
                        <label class="block mb-1.5">Peran Jabatan (Role)</label>
                        <select name="role" required class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                            <option value="kasir">Kasir Resto</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-[#4A2C2A] hover:bg-orange-600 text-white font-bold py-3 rounded-xl uppercase tracking-wider transition shadow">
                        Daftarkan Staf
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ================== TAB 5: ULASAN PELANGGAN ================== -->
    <div id="section-feedback" class="space-y-8 hidden">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                <h2 class="font-bold text-xs uppercase text-[#4A2C2A] tracking-wider">Daftar Ulasan & Feedback Pelanggan</h2>
                <span class="text-xs text-gray-400 font-semibold">Total Ulasan: {{ count($feedbacks) }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50 border-b border-gray-100">
                        <tr class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            <th class="px-6 py-3">Tanggal / ID Booking</th>
                            <th class="px-6 py-3">Pelanggan</th>
                            <th class="px-6 py-3">Meja</th>
                            <th class="px-6 py-3">Rating</th>
                            <th class="px-6 py-3">Ulasan / Komentar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs font-semibold text-gray-700">
                        @forelse($feedbacks as $fb)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ date('d M Y H:i', strtotime($fb->tanggal)) }}</div>
                                    <div class="text-[10px] text-gray-400">#RES-{{ $fb->reservasi_id }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-[#4A2C2A]">{{ $fb->user->nama ?? 'Pelanggan' }}</div>
                                    <div class="text-[10px] text-gray-400 font-normal">{{ $fb->user->email }}</div>
                                </td>
                                <td class="px-6 py-4 font-bold text-orange-600">
                                    {{ $fb->reservasi->meja->nomor_meja ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex text-yellow-400">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ $i <= $fb->rating ? 'fill-current text-yellow-400' : 'text-gray-200' }}" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endfor
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-normal leading-relaxed italic max-w-sm">
                                    "{{ $fb->komentar }}"
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-gray-500 font-medium">Belum ada ulasan masuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================== TAB 6: PENGATURAN RESTO ================== -->
    <div id="section-settings" class="space-y-8 hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- QRIS Upload Card -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 space-y-6">
                <h3 class="text-lg font-bold text-[#4A2C2A] uppercase tracking-wider border-b border-gray-100 pb-3">QRIS Pembayaran</h3>
                <p class="text-xs text-gray-500">Unggah kode QRIS aktif yang akan dipindai oleh pelanggan saat melakukan reservasi meja dan pemesanan menu.</p>
                
                <div class="flex flex-col items-center gap-4 bg-[#FFF5EE] p-6 rounded-2xl border border-[#FFE4D3]">
                    <div class="font-bold text-xs text-orange-700 uppercase tracking-widest mb-1">QRIS Aktif Saat Ini</div>
                    <img src="{{ route('storage.file', ['path' => ($qris_image ?? 'qris/default_qris.jpg')]) }}" alt="QRIS Active" class="max-w-[200px] h-auto rounded-lg shadow border border-gray-100 bg-white p-2">
                </div>

                <form action="{{ route('admin.settings.qris') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-semibold text-gray-700">
                    @csrf
                    <div>
                        <label class="block mb-1.5 font-bold text-[#4A2C2A]">Unggah QRIS Baru</label>
                        <input type="file" name="qris_image" accept="image/*" required
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition text-xs file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-bold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer">
                    </div>
                    <button type="submit" class="w-full bg-[#4A2C2A] hover:bg-orange-600 text-white font-bold py-3 rounded-xl uppercase tracking-wider transition shadow">
                        Perbarui Gambar QRIS
                    </button>
                </form>
            </div>

            <!-- Change Password Card (Self) -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 space-y-6">
                <h3 class="text-lg font-bold text-[#4A2C2A] uppercase tracking-wider border-b border-gray-100 pb-3">Ubah Password Akun</h3>
                <p class="text-xs text-gray-500">Ubah password akun Anda untuk menjaga keamanan akses ke panel kontrol.</p>
                
                <form action="{{ route('change.password') }}" method="POST" class="space-y-4 text-xs font-semibold text-gray-700">
                    @csrf
                    <div>
                        <label class="block mb-1.5 font-bold text-[#4A2C2A]">Password Saat Ini</label>
                        <input type="password" name="current_password" required placeholder="Masukkan password Anda saat ini"
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                    </div>
                    <div>
                        <label class="block mb-1.5 font-bold text-[#4A2C2A]">Password Baru</label>
                        <input type="password" name="new_password" required placeholder="Minimal 8 karakter"
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                    </div>
                    <div>
                        <label class="block mb-1.5 font-bold text-[#4A2C2A]">Konfirmasi Password Baru</label>
                        <input type="password" name="new_password_confirmation" required placeholder="Ulangi password baru"
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                    </div>
                    <button type="submit" class="w-full bg-[#4A2C2A] hover:bg-orange-600 text-white font-bold py-3 rounded-xl uppercase tracking-wider transition shadow">
                        Ubah Password Saya
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- ================== MODALS EDIT ================== -->

<!-- 1. Menu Edit Modal -->
<div id="modal-edit-menu" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div onclick="closeMenuEditModal()" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative inline-block align-middle bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full border border-gray-100">
            <div class="bg-[#4A2C2A] px-6 py-4 flex justify-between items-center text-white">
                <h3 class="text-sm font-bold uppercase tracking-wider text-[#FFF5EE]">Ubah Menu Hidangan</h3>
                <button onclick="closeMenuEditModal()" class="text-gray-400 hover:text-white transition text-xl">&times;</button>
            </div>
            
            <form id="form-edit-menu" action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 text-xs font-semibold text-gray-700">
                @csrf
                @method('PUT')
                <div>
                    <label class="block mb-1.5">Nama Menu</label>
                    <input type="text" name="nama_menu" id="edit-menu-name" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                </div>
                <div>
                    <label class="block mb-1.5">Kategori</label>
                    <select name="id_kategori" id="edit-menu-category" required class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id_kategori }}">{{ $cat->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1.5">Harga (Rp)</label>
                        <input type="number" name="harga" id="edit-menu-price" min="0" required
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                    </div>
                    <div>
                        <label class="block mb-1.5">Stok</label>
                        <input type="number" name="stok" id="edit-menu-stock" min="0" required
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                    </div>
                </div>
                <div>
                    <label class="block mb-1.5">Gambar Hidangan Baru (Opsional)</label>
                    <input type="file" name="gambar" accept="image/*"
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition text-xs file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-bold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer">
                </div>
                <div>
                    <label class="block mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" id="edit-menu-desc" required rows="3"
                              class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition"></textarea>
                </div>
                <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 rounded-xl uppercase tracking-wider transition shadow">
                    Perbarui Menu
                </button>
            </form>
        </div>
    </div>
</div>

<!-- 2. Category Edit Modal -->
<div id="modal-edit-category" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div onclick="closeCategoryEditModal()" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative inline-block align-middle bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full border border-gray-100">
            <div class="bg-[#4A2C2A] px-6 py-4 flex justify-between items-center text-white">
                <h3 class="text-sm font-bold uppercase tracking-wider text-[#FFF5EE]">Ubah Kategori</h3>
                <button onclick="closeCategoryEditModal()" class="text-gray-400 hover:text-white transition text-xl">&times;</button>
            </div>
            
            <form id="form-edit-category" action="" method="POST" class="p-6 space-y-4 text-xs font-semibold text-gray-700">
                @csrf
                @method('PUT')
                <div>
                    <label class="block mb-1.5">Nama Kategori</label>
                    <input type="text" name="nama_kategori" id="edit-cat-name" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                </div>
                <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 rounded-xl uppercase tracking-wider transition shadow">
                    Perbarui Kategori
                </button>
            </form>
        </div>
    </div>
</div>

<!-- 3. Table Edit Modal -->
<div id="modal-edit-table" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div onclick="closeTableEditModal()" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative inline-block align-middle bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full border border-gray-100">
            <div class="bg-[#4A2C2A] px-6 py-4 flex justify-between items-center text-white">
                <h3 class="text-sm font-bold uppercase tracking-wider text-[#FFF5EE]">Ubah Meja Makan</h3>
                <button onclick="closeTableEditModal()" class="text-gray-400 hover:text-white transition text-xl">&times;</button>
            </div>
            
            <form id="form-edit-table" action="" method="POST" class="p-6 space-y-4 text-xs font-semibold text-gray-700">
                @csrf
                @method('PUT')
                <div>
                    <label class="block mb-1.5">Nomor Meja</label>
                    <input type="text" name="nomor_meja" id="edit-table-no" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                </div>
                <div>
                    <label class="block mb-1.5">Kapasitas (Orang)</label>
                    <input type="number" name="kapasitas" id="edit-table-cap" min="1" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                </div>
                <div>
                    <label class="block mb-1.5">Status</label>
                    <select name="status" id="edit-table-status" required class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                        <option value="tersedia">Tersedia</option>
                        <option value="dipesan">Dipesan</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 rounded-xl uppercase tracking-wider transition shadow">
                    Perbarui Meja
                </button>
            </form>
        </div>
    </div>
</div>

<!-- 4. Staff Edit Modal -->
<div id="modal-edit-staff" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div onclick="closeStaffEditModal()" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative inline-block align-middle bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full border border-gray-100">
            <div class="bg-[#4A2C2A] px-6 py-4 flex justify-between items-center text-white">
                <h3 class="text-sm font-bold uppercase tracking-wider text-[#FFF5EE]">Ubah Data Staf</h3>
                <button onclick="closeStaffEditModal()" class="text-gray-400 hover:text-white transition text-xl">&times;</button>
            </div>
            
            <form id="form-edit-staff" action="" method="POST" class="p-6 space-y-4 text-xs font-semibold text-gray-700">
                @csrf
                @method('PUT')
                <div>
                    <label class="block mb-1.5">Nama Lengkap</label>
                    <input type="text" name="nama" id="edit-staff-name" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                </div>
                <div>
                    <label class="block mb-1.5">Email</label>
                    <input type="email" name="email" id="edit-staff-email" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                </div>
                <div>
                    <label class="block mb-1.5">No. HP / Telepon</label>
                    <input type="text" name="no_hp" id="edit-staff-phone" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                </div>
                <div>
                    <label class="block mb-1.5">Password Baru (Kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" minlength="8" placeholder="Masukkan password baru"
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                </div>
                <div>
                    <label class="block mb-1.5">Peran Jabatan (Role)</label>
                    <select name="role" id="edit-staff-role" required class="w-full px-3 py-2 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-600 transition">
                        <option value="kasir">Kasir Resto</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 rounded-xl uppercase tracking-wider transition shadow">
                    Perbarui Staf
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Tab and Modal switching logic -->
<script>
    function switchTab(tabName) {
        // Toggle sections
        document.getElementById('section-menu').classList.add('hidden');
        document.getElementById('section-category').classList.add('hidden');
        document.getElementById('section-table').classList.add('hidden');
        document.getElementById('section-staff').classList.add('hidden');
        document.getElementById('section-feedback').classList.add('hidden');
        document.getElementById('section-settings').classList.add('hidden');
        
        document.getElementById(`section-${tabName}`).classList.remove('hidden');

        // Toggle tab button active styles
        const tabs = ['menu', 'category', 'table', 'staff', 'feedback', 'settings'];
        tabs.forEach(t => {
            const btn = document.getElementById(`tab-${t}`);
            if (t === tabName) {
                btn.className = "px-6 py-3 text-orange-600 border-b-2 border-orange-600 transition outline-none";
            } else {
                btn.className = "px-6 py-3 text-gray-500 hover:text-orange-600 transition outline-none";
            }
        });

        // Store tab in session storage to keep state on refresh
        sessionStorage.setItem('active_admin_tab', tabName);
    }

    // Restore active tab on load
    document.addEventListener('DOMContentLoaded', () => {
        const activeTab = sessionStorage.getItem('active_admin_tab') || 'menu';
        switchTab(activeTab);
    });

    // 1. Menu Modal Functions
    function openMenuEditModal(id, name, catId, price, stock, desc) {
        document.getElementById('form-edit-menu').action = `/admin/menus/${id}`;
        document.getElementById('edit-menu-name').value = name;
        document.getElementById('edit-menu-category').value = catId;
        document.getElementById('edit-menu-price').value = Math.round(price);
        document.getElementById('edit-menu-stock').value = stock;
        document.getElementById('edit-menu-desc').value = desc;
        document.getElementById('modal-edit-menu').classList.remove('hidden');
    }
    function closeMenuEditModal() {
        document.getElementById('modal-edit-menu').classList.add('hidden');
    }

    // 2. Category Modal Functions
    function openCategoryEditModal(id, name) {
        document.getElementById('form-edit-category').action = `/admin/categories/${id}`;
        document.getElementById('edit-cat-name').value = name;
        document.getElementById('modal-edit-category').classList.remove('hidden');
    }
    function closeCategoryEditModal() {
        document.getElementById('modal-edit-category').classList.add('hidden');
    }

    // 3. Table Modal Functions
    function openTableEditModal(id, no, cap, status) {
        document.getElementById('form-edit-table').action = `/admin/tables/${id}`;
        document.getElementById('edit-table-no').value = no;
        document.getElementById('edit-table-cap').value = cap;
        document.getElementById('edit-table-status').value = status;
        document.getElementById('modal-edit-table').classList.remove('hidden');
    }
    function closeTableEditModal() {
        document.getElementById('modal-edit-table').classList.add('hidden');
    }

    // 4. Staff Modal Functions
    function openStaffEditModal(id, nama, email, no_hp, role) {
        document.getElementById('form-edit-staff').action = `/admin/staff/${id}`;
        document.getElementById('edit-staff-name').value = nama;
        document.getElementById('edit-staff-email').value = email;
        document.getElementById('edit-staff-phone').value = no_hp;
        document.getElementById('edit-staff-role').value = role;
        
        // Superadmin safety check in frontend UI
        const roleSelect = document.getElementById('edit-staff-role');
        const emailInput = document.getElementById('edit-staff-email');
        if (email === 'admin@restofeasto.com') {
            roleSelect.disabled = true;
            emailInput.readOnly = true;
        } else {
            roleSelect.disabled = false;
            emailInput.readOnly = false;
        }
        
        document.getElementById('modal-edit-staff').classList.remove('hidden');
    }
    function closeStaffEditModal() {
        document.getElementById('modal-edit-staff').classList.add('hidden');
    }
</script>
@endsection