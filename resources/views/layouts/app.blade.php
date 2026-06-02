<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestoFeasto - Nusantara</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

    <nav class="sticky top-0 z-50 bg-white border-b border-gray-100 shadow-sm">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            
            <a href="{{ route('beranda') }}" class="flex items-center">
                <span class="text-2xl font-serif font-bold tracking-tighter">
                    <span class="text-[#2D3E50]">Resto</span><span class="text-[#FF6B00]">Feasto</span>
                </span>
            </a>

            <div class="hidden md:flex items-center gap-8">
                @if(Auth::check())
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700 transition uppercase tracking-[0.2em]">PANEL ADMIN</a>
                    @elseif(Auth::user()->role === 'kasir')
                        <a href="{{ route('kasir.dashboard') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700 transition uppercase tracking-[0.2em]">PANEL KASIR</a>
                    @else
                        <a href="{{ route('beranda') }}" class="text-xs font-bold text-[#4A2C2A] hover:text-orange-600 transition uppercase tracking-[0.2em]">BERANDA</a>
                        <a href="/beranda#menu-section" class="text-xs font-bold text-gray-400 hover:text-orange-600 transition uppercase tracking-[0.2em]">MENU NUSANTARA</a>
                        <a href="{{ route('reservasi') }}" class="text-xs font-bold text-gray-400 hover:text-orange-600 transition uppercase tracking-[0.2em]">RESERVASI</a>
                        <a href="{{ route('pembayaran') }}" class="text-xs font-bold text-gray-400 hover:text-orange-600 transition uppercase tracking-[0.2em]">RIWAYAT & BAYAR</a>
                    @endif
                @else
                    <a href="{{ route('beranda') }}" class="text-xs font-bold text-[#4A2C2A] hover:text-orange-600 transition uppercase tracking-[0.2em]">BERANDA</a>
                    <a href="/#menu-section" class="text-xs font-bold text-gray-400 hover:text-orange-600 transition uppercase tracking-[0.2em]">MENU</a>
                @endif
            </div>

            <div class="flex items-center gap-4">
                @if(Auth::check() && Auth::user()->role === 'pelanggan')
                    <!-- Cart Trigger Button -->
                    <button onclick="toggleCartDrawer()" class="relative p-2 bg-[#FFF5EE] hover:bg-orange-100 text-[#FF6B00] rounded-full transition shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span id="cart-badge" class="absolute -top-1 -right-1 w-5 h-5 bg-red-600 text-white rounded-full text-[10px] font-bold flex items-center justify-center border border-white {{ count(session('cart', [])) > 0 ? '' : 'hidden' }}">
                            {{ count(session('cart', [])) }}
                        </span>
                    </button>
                @endif

                @if(!Auth::check())
                    <a href="{{ route('login') }}" class="text-xs font-bold bg-[#4A2C2A] text-white px-5 py-2 rounded-full hover:bg-black transition uppercase">LOGIN</a>
                    <a href="{{ route('register') }}" class="text-xs font-bold border-2 border-[#4A2C2A] text-[#4A2C2A] px-5 py-1.5 rounded-full hover:bg-gray-100 transition uppercase">REGISTER</a>
                @else
                    <div class="flex items-center gap-4 bg-[#FFF5EE] px-4 py-1.5 rounded-full border border-[#FFE4D3]">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-[#FF6B00] rounded-full flex items-center justify-center text-white font-bold text-sm">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <span class="text-sm font-bold text-[#4A2C2A]">
                                Halo, {{ strtok(Auth::user()->name, ' ') }}! 
                                <span class="text-[10px] uppercase bg-orange-200 text-orange-800 px-1.5 py-0.5 rounded ml-1 font-semibold">{{ Auth::user()->role }}</span>
                            </span>
                        </div>
                        
                        <div class="h-5 w-[1px] bg-[#FFDAB9]"></div>
                        
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-[#D93025] hover:text-red-700 transition uppercase">KELUAR</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- Flash Notifications -->
    <div class="container mx-auto px-6 mt-4">
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm flex items-center justify-between mb-4">
                <div>{{ session('success') }}</div>
                <button onclick="this.parentElement.remove()" class="text-green-700 font-bold hover:text-green-950">&times;</button>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm flex items-center justify-between mb-4">
                <div>{{ session('error') }}</div>
                <button onclick="this.parentElement.remove()" class="text-red-700 font-bold hover:text-red-950">&times;</button>
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl shadow-sm mb-4">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-bold text-sm mb-1">Terjadi Kesalahan:</p>
                        <ul class="list-disc list-inside text-xs space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-red-700 font-bold hover:text-red-950">&times;</button>
                </div>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-[#4A2C2A] text-white py-12 border-t-8 border-orange-600 mt-16">
        <div class="container mx-auto px-6 text-center">
            <h2 class="font-serif text-2xl font-bold tracking-widest text-[#FFF5EE] mb-4">RESTOFEASTO</h2>
            <p class="text-xs text-gray-400 max-w-md mx-auto mb-6">Warisan cita rasa kuliner Nusantara legendaris dengan pelayanan digital terbaik.</p>
            <div class="text-[11px] font-semibold uppercase tracking-[0.2em] text-orange-400">
                INFORMASI KONTAK | MEDAN | &copy; 2026 RESTOFEASTO NUSANTARA
            </div>
        </div>
    </footer>

    <!-- Cart Drawer Modal -->
    <div id="cart-drawer" class="fixed inset-0 z-50 overflow-hidden hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 overflow-hidden">
            <!-- Background overlay -->
            <div onclick="toggleCartDrawer()" class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div class="pointer-events-auto w-screen max-w-md">
                    <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                        <!-- Drawer Header -->
                        <div class="flex items-start justify-between bg-[#4A2C2A] px-6 py-6 text-white">
                            <h2 class="text-lg font-serif font-bold uppercase tracking-wider text-[#FFF5EE]" id="slide-over-title">Keranjang Belanja</h2>
                            <div class="ml-3 flex h-7 items-center">
                                <button type="button" onclick="toggleCartDrawer()" class="relative -m-2 p-2 text-gray-400 hover:text-white transition">
                                    <span class="absolute -inset-0.5"></span>
                                    <span class="sr-only">Tutup</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Drawer Body / Cart Items -->
                        <div class="flex-1 px-6 py-6" id="cart-drawer-items">
                            @php $cart = session('cart', []); @endphp
                            @if(empty($cart))
                                <div class="flex flex-col items-center justify-center h-64 text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    <p class="text-gray-500 font-medium">Keranjang belanja Anda kosong</p>
                                    <p class="text-xs text-gray-400 mt-1">Tambahkan menu hidangan Nusantara lezat untuk memesan.</p>
                                </div>
                            @else
                                <div class="flow-root">
                                    <ul role="list" class="-my-6 divide-y divide-gray-100">
                                        @php $totalPrice = 0; @endphp
                                        @foreach($cart as $item)
                                            @php $totalPrice += $item['harga'] * $item['qty']; @endphp
                                            <li class="flex py-6">
                                                <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md border border-gray-200 bg-[#FFF5EE] flex items-center justify-center text-[10px] font-bold text-orange-600">
                                                    Feasto
                                                </div>
                                                <div class="ml-4 flex flex-1 flex-col">
                                                    <div>
                                                        <div class="flex justify-between text-sm font-semibold text-gray-900">
                                                            <h3>{{ $item['nama_menu'] }}</h3>
                                                            <p class="ml-4 text-orange-600 font-bold">Rp {{ number_format($item['harga'] * $item['qty'], 0, ',', '.') }}</p>
                                                        </div>
                                                        <p class="mt-1 text-xs text-gray-400">Harga satuan: Rp {{ number_format($item['harga'], 0, ',', '.') }}</p>
                                                    </div>
                                                    <div class="flex flex-1 items-end justify-between text-xs">
                                                        <div class="flex items-center border border-gray-200 rounded">
                                                            <button onclick="updateCartQty({{ $item['menu_id'] }}, {{ $item['qty'] - 1 }})" class="px-2 py-0.5 bg-gray-50 hover:bg-gray-100 font-bold">-</button>
                                                            <span class="px-3 py-0.5 bg-white text-gray-700 font-semibold">{{ $item['qty'] }}</span>
                                                            <button onclick="updateCartQty({{ $item['menu_id'] }}, {{ $item['qty'] + 1 }}, {{ $item['stok'] }})" class="px-2 py-0.5 bg-gray-50 hover:bg-gray-100 font-bold">+</button>
                                                        </div>
                                                        <div class="flex">
                                                            <button type="button" onclick="removeFromCart({{ $item['menu_id'] }})" class="font-semibold text-red-600 hover:text-red-500">Hapus</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <!-- Drawer Footer -->
                        @if(!empty($cart))
                            <div class="border-t border-gray-200 px-6 py-6 sm:px-6 bg-gray-50">
                                <div class="flex justify-between text-base font-bold text-[#4A2C2A]">
                                    <p>Total Harga</p>
                                    <p>Rp {{ number_format($totalPrice, 0, ',', '.') }}</p>
                                </div>
                                <p class="mt-0.5 text-xs text-gray-400">Pemesanan tempat (meja) akan divalidasi pada langkah berikutnya.</p>
                                <div class="mt-6">
                                    <a href="{{ route('reservasi') }}" class="flex items-center justify-center rounded-md border border-transparent bg-orange-600 px-6 py-3 text-base font-bold text-white shadow-md hover:bg-orange-700 transition uppercase tracking-wider">Lanjutkan Ke Reservasi</a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Scripts for Cart Actions -->
    <script>
        function toggleCartDrawer() {
            const drawer = document.getElementById('cart-drawer');
            drawer.classList.toggle('hidden');
        }

        function addToCart(menuId, namaMenu, harga, stok) {
            fetch("{{ route('cart.add') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ menu_id: menuId, nama_menu: namaMenu, harga: harga, stok: stok })
            })
            .then(res => {
                if(!res.ok) {
                    return res.json().then(err => { throw new Error(err.message || 'Gagal menambahkan ke keranjang') });
                }
                return res.json();
            })
            .then(data => {
                // Refresh cart drawer and update badge
                updateCartUI(data.cart);
                // Open drawer for visual confirmation
                toggleCartDrawer();
            })
            .catch(err => {
                alert(err.message);
            });
        }

        function updateCartQty(menuId, qty, stok = 999) {
            if (qty > stok) {
                alert("Stok menu tidak mencukupi.");
                return;
            }
            fetch("{{ route('cart.update') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ menu_id: menuId, qty: qty })
            })
            .then(res => res.json())
            .then(data => {
                updateCartUI(data.cart);
            });
        }

        function removeFromCart(menuId) {
            fetch("{{ route('cart.remove') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ menu_id: menuId })
            })
            .then(res => res.json())
            .then(data => {
                updateCartUI(data.cart);
            });
        }

        function updateCartUI(cart) {
            const keys = Object.keys(cart);
            const badge = document.getElementById('cart-badge');
            
            if (keys.length > 0) {
                badge.innerText = keys.length;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }

            // Reload the page content if we are in the reservation flow so that the checkout matches
            if (window.location.pathname === '/reservasi') {
                window.location.reload();
                return;
            }

            // Dynamically redraw items in drawer
            const drawerItems = document.getElementById('cart-drawer-items');
            if (keys.length === 0) {
                drawerItems.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-64 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <p class="text-gray-500 font-medium">Keranjang belanja Anda kosong</p>
                        <p class="text-xs text-gray-400 mt-1">Tambahkan menu hidangan Nusantara lezat untuk memesan.</p>
                    </div>`;
                
                // Hide footer
                const footer = drawerItems.nextElementSibling;
                if (footer) footer.remove();
            } else {
                let listHtml = '<div class="flow-root"><ul role="list" class="-my-6 divide-y divide-gray-100">';
                let totalPrice = 0;
                
                keys.forEach(id => {
                    const item = cart[id];
                    totalPrice += item.harga * item.qty;
                    const subtotalFormatted = new Intl.NumberFormat('id-ID').format(item.harga * item.qty);
                    const hargaFormatted = new Intl.NumberFormat('id-ID').format(item.harga);
                    
                    listHtml += `
                        <li class="flex py-6">
                            <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md border border-gray-200 bg-[#FFF5EE] flex items-center justify-center text-[10px] font-bold text-orange-600">
                                Feasto
                            </div>
                            <div class="ml-4 flex flex-1 flex-col">
                                <div>
                                    <div class="flex justify-between text-sm font-semibold text-gray-900">
                                        <h3>${item.nama_menu}</h3>
                                        <p class="ml-4 text-orange-600 font-bold">Rp ${subtotalFormatted}</p>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-400 font-semibold text-gray-500">Harga: Rp ${hargaFormatted}</p>
                                </div>
                                <div class="flex flex-1 items-end justify-between text-xs">
                                    <div class="flex items-center border border-gray-200 rounded">
                                        <button onclick="updateCartQty(${item.menu_id}, ${item.qty - 1})" class="px-2 py-0.5 bg-gray-50 hover:bg-gray-100 font-bold">-</button>
                                        <span class="px-3 py-0.5 bg-white text-gray-700 font-semibold">${item.qty}</span>
                                        <button onclick="updateCartQty(${item.menu_id}, ${item.qty + 1}, ${item.stok})" class="px-2 py-0.5 bg-gray-50 hover:bg-gray-100 font-bold">+</button>
                                    </div>
                                    <div class="flex">
                                        <button type="button" onclick="removeFromCart(${item.menu_id})" class="font-semibold text-red-600 hover:text-red-500">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        </li>`;
                });
                
                listHtml += '</ul></div>';
                drawerItems.innerHTML = listHtml;
                
                // Add or update footer
                const totalPriceFormatted = new Intl.NumberFormat('id-ID').format(totalPrice);
                let footer = drawerItems.nextElementSibling;
                if (!footer || !footer.classList.contains('px-6')) {
                    footer = document.createElement('div');
                    footer.className = "border-t border-gray-200 px-6 py-6 sm:px-6 bg-gray-50";
                    drawerItems.parentNode.appendChild(footer);
                }
                
                footer.innerHTML = `
                    <div class="flex justify-between text-base font-bold text-[#4A2C2A]">
                        <p>Total Harga</p>
                        <p>Rp ${totalPriceFormatted}</p>
                    </div>
                    <p class="mt-0.5 text-xs text-gray-400">Pemesanan tempat (meja) akan divalidasi pada langkah berikutnya.</p>
                    <div class="mt-6">
                        <a href="{{ route('reservasi') }}" class="flex items-center justify-center rounded-md border border-transparent bg-orange-600 px-6 py-3 text-base font-bold text-white shadow-md hover:bg-orange-700 transition uppercase tracking-wider">Lanjutkan Ke Reservasi</a>
                    </div>`;
            }
        }
    </script>
</body>
</html>