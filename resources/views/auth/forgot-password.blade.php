@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white p-10 rounded-2xl shadow-xl border border-gray-100">
        
        <div class="text-center mb-10">
            <h2 class="text-3xl font-serif font-bold text-[#4A2C2A]">
                Lupa Password
            </h2>
            <div class="h-1 w-12 bg-orange-600 mx-auto mt-3"></div>
            <p class="text-gray-500 text-sm mt-4">
                Masukkan alamat email Anda untuk menerima link reset password.
        </div>

        <form action="{{ route('password.email') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1 text-left">Email</label>
                <input id="email" 
                       name="email" 
                       type="email" 
                       required 
                       value="{{ old('email') }}"
                       class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-600 focus:border-orange-600 outline-none transition" 
                       placeholder="Masukkan email Anda">
            </div>

            <button type="submit" class="w-full bg-[#4A2C2A] text-white py-3.5 rounded-lg font-bold hover:bg-black transition shadow-lg uppercase tracking-widest mt-4">
                Kirim Link Reset
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-gray-600">
            Kembali ke <a href="{{ route('login') }}" class="font-bold text-orange-600 hover:text-orange-500 transition">Halaman Login</a>
        </p>
    </div>
</div>
@endsection
