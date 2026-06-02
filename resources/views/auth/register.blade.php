@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white p-10 rounded-2xl shadow-xl border border-gray-100">
        
        <div class="text-center mb-8">
            <h2 class="text-3xl font-serif font-bold text-[#4A2C2A]">Daftar Akun Baru</h2>
            <div class="h-1 w-12 bg-orange-600 mx-auto mt-3"></div>
            <p class="text-gray-500 text-sm mt-4">Mulai perjalanan kuliner Anda bersama RestoFeasto.</p>
        </div>

        <form action="/register" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 text-left">Nama Lengkap</label>
                <input name="name" type="text" required 
                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-600 focus:border-orange-600 outline-none transition"
                       placeholder="Masukkan nama lengkap">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 text-left">Email</label>
                <input name="email" type="email" required 
                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-600 focus:border-orange-600 outline-none transition"
                       placeholder="nama@email.com">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 text-left">No. Telepon</label>
                <input name="phone" type="tel" required 
                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-600 focus:border-orange-600 outline-none transition" 
                       placeholder="08xxxxxxxxxx">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 text-left">Password</label>
                <div class="relative">
                    <input id="password" name="password" type="password" required autocomplete="new-password" 
                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-600 focus:border-orange-600 outline-none transition"
                           placeholder="Buat password minimal 8 karakter">
                    
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-orange-600 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full bg-[#4A2C2A] text-white py-3.5 rounded-lg font-bold hover:bg-black transition shadow-lg mt-6 uppercase tracking-widest">
                Daftar Akun
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-gray-600">
            Sudah punya akun? <a href="/login" class="font-bold text-orange-600 hover:text-orange-500 transition">Masuk di sini</a>
        </p>
    </div>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        this.classList.toggle('text-orange-600');
    });
</script>
@endsection