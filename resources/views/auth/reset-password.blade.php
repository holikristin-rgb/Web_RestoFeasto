@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white p-10 rounded-2xl shadow-xl border border-gray-100">
        
        <div class="text-center mb-10">
            <h2 class="text-3xl font-serif font-bold text-[#4A2C2A]">
                Reset Password
            </h2>
            <div class="h-1 w-12 bg-orange-600 mx-auto mt-3"></div>
            <p class="text-gray-500 text-sm mt-4">
                Buat password baru yang aman untuk akun Anda.
        </div>

        <form action="{{ route('password.update') }}" method="POST" class="space-y-6">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1 text-left">Email</label>
                <input id="email" 
                       name="email" 
                       type="email" 
                       required 
                       readonly
                       value="{{ $email ?? old('email') }}"
                       class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-gray-50 text-gray-500 outline-none transition" 
                       placeholder="Masukkan email Anda">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1 text-left">Password Baru</label>
                <input id="password" 
                       name="password" 
                       type="password" 
                       required 
                       class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-600 focus:border-orange-600 outline-none transition" 
                       placeholder="Minimal 8 karakter">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1 text-left">Konfirmasi Password Baru</label>
                <input id="password_confirmation" 
                       name="password_confirmation" 
                       type="password" 
                       required 
                       class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-600 focus:border-orange-600 outline-none transition" 
                       placeholder="Masukkan kembali password baru">
            </div>

            <button type="submit" class="w-full bg-[#4A2C2A] text-white py-3.5 rounded-lg font-bold hover:bg-black transition shadow-lg uppercase tracking-widest mt-4">
                Perbarui Password
            </button>
        </form>
    </div>
</div>
@endsection
