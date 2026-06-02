@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white p-10 rounded-2xl shadow-xl border border-gray-100">
        
        <div class="text-center mb-10">
            <h2 class="text-3xl font-serif font-bold text-[#4A2C2A]">
                Verifikasi OTP
            </h2>
            <div class="h-1 w-12 bg-orange-600 mx-auto mt-3"></div>
            <p class="text-gray-500 text-sm mt-4">
                Masukkan 6 digit kode verifikasi yang telah kami kirimkan ke email Anda.
            </p>
            @if(isset($email) && $email)
                <p class="text-orange-600 font-semibold text-sm mt-1">
                    {{ $email }}
                </p>
            @endif
        </div>

        <form action="{{ route('verify.otp') }}" method="POST" class="space-y-6">
            @csrf
            
            <input type="hidden" name="email" value="{{ $email ?? '' }}">

            <div>
                <label for="otp_code" class="block text-sm font-medium text-gray-700 mb-1 text-center font-bold">Kode OTP</label>
                <input id="otp_code" 
                       name="otp_code" 
                       type="text" 
                       required 
                       maxlength="6"
                       pattern="\d{6}"
                       class="w-full px-4 py-3 text-center text-2xl font-bold tracking-[0.5em] rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-600 focus:border-orange-600 outline-none transition" 
                       placeholder="000000">
            </div>

            <button type="submit" class="w-full bg-[#4A2C2A] text-white py-3.5 rounded-lg font-bold hover:bg-black transition shadow-lg uppercase tracking-widest mt-4">
                Verifikasi Akun
            </button>
        </form>

        <form action="{{ route('verify.otp.resend') }}" method="POST" class="mt-6 text-center">
            @csrf
            <input type="hidden" name="email" value="{{ $email ?? '' }}">
            <p class="text-sm text-gray-600">
                Tidak menerima kode? 
                <button type="submit" class="font-bold text-orange-600 hover:text-orange-500 transition bg-transparent border-none cursor-pointer">
                    Kirim Ulang OTP
                </button>
            </p>
        </form>
    </div>
</div>
@endsection
