@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white p-10 rounded-2xl shadow-xl border border-gray-100">
        
        <div class="text-center mb-10">
            <h2 class="text-3xl font-serif font-bold text-[#4A2C2A]">
                Login {{ $role ?? 'Pelanggan' }}
            </h2>
            <div class="h-1 w-12 bg-orange-600 mx-auto mt-3"></div>
            <p class="text-gray-500 text-sm mt-4">
                {{ isset($role) && $role !== 'Pelanggan' ? 'Akses Khusus Staf RestoFeasto' : 'Selamat datang kembali! Silakan masuk ke akun Anda.' }}
            </p>
        </div>

        <form action="/login" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1 text-left">Email / Username</label>
                <input id="username" 
                       name="username" 
                       type="text" 
                       required 
                       value="{{ $default_email ?? '' }}" 
                       autocomplete="{{ isset($role) && $role !== 'Pelanggan' ? 'on' : 'off' }}"
                       class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-600 focus:border-orange-600 outline-none transition" 
                       placeholder="Masukkan email atau username">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1 text-left">Password</label>
                <div class="relative">
                    <input id="password" 
                           name="password" 
                           type="password" 
                           required 
                           value="" 
                           autocomplete="new-password"
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-600 focus:border-orange-600 outline-none transition" 
                           placeholder="{{ isset($role) && $role !== 'Pelanggan' ? 'Masukkan password khusus ' . $role : 'Masukkan password' }}">
                    
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-orange-600 transition-colors">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded cursor-pointer">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-700 cursor-pointer">Ingat saya</label>
                </div>
                @if(!isset($role) || $role === 'Pelanggan')
                <div class="text-sm">
                    <a href="/forgot-password" class="font-medium text-orange-600 hover:text-orange-500 transition">Lupa password?</a>
                </div>
                @endif
            </div>

            <button type="submit" class="w-full bg-[#4A2C2A] text-white py-3.5 rounded-lg font-bold hover:bg-black transition shadow-lg uppercase tracking-widest mt-4">
                MASUK SEBAGAI {{ strtoupper($role ?? 'PELANGGAN') }}
            </button>
        </form>

        @if(!isset($role) || $role === 'Pelanggan')
        <p class="mt-8 text-center text-sm text-gray-600">
            Belum punya akun? <a href="/register" class="font-bold text-orange-600 hover:text-orange-500 transition">Daftar sekarang</a>
        </p>
        @endif
    </div>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const passwordField = document.querySelector('#password');
    
    togglePassword.addEventListener('click', function () {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        
        this.classList.toggle('text-orange-600');
    });
</script>
@endsection