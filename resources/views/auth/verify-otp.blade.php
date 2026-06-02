@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-gray-50 to-orange-50/30">
    
    <div class="max-w-md w-full space-y-4">
        
        @if(session('success') || session('status') || !request()->isMethod('post')) 
            <div class="flex items-center p-4 bg-emerald-50 border border-emerald-100 rounded-2xl shadow-sm text-emerald-800 animate-fade-in transition duration-300">
                <div class="p-2 bg-emerald-500 text-white rounded-xl text-xs mr-3.5 shadow-sm shadow-emerald-200 flex items-center justify-center w-6 h-6">
                    ✓
                </div>
                <div class="flex-1 text-xs sm:text-sm font-medium tracking-wide text-emerald-800">
                    Registrasi berhasil! Silakan masukkan kode OTP yang dikirim ke email Anda.
                </div>
            </div>
        @endif

        <div class="bg-white p-10 rounded-3xl shadow-xl border border-gray-100/80 backdrop-blur-sm">
            
            <div class="text-center mb-8">
                <div class="mx-auto h-16 w-16 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-600 text-2xl shadow-inner mb-6">
                    📩
                </div>
                <h2 class="text-3xl font-serif font-black text-[#4A2C2A] tracking-tight">
                    Verifikasi OTP
                </h2>
                <div class="h-1 w-12 bg-orange-500 mx-auto mt-3 rounded-full"></div>
                
                <p class="text-gray-500 text-sm mt-4 font-light leading-relaxed">
                    Masukkan 6 digit kode verifikasi yang telah kami kirimkan ke email Anda.
                </p>
                @if(isset($email) && $email)
                    <p class="text-orange-600 font-semibold text-sm mt-1 select-all">
                        {{ $email }}
                    </p>
                @endif
            </div>

            <form action="{{ route('verify.otp') }}" method="POST" class="space-y-6" id="otp-form">
                @csrf
                <input type="hidden" name="email" value="{{ $email ?? '' }}">
                <input type="hidden" name="otp_code" id="otp_code">

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest text-center mb-4">
                        Kode OTP
                    </label>
                    
                    <div class="flex justify-between gap-2 sm:gap-3" id="otp-inputs-container">
                        <input type="text" maxlength="1" pattern="\d*" inputmode="numeric" class="w-12 h-14 text-center text-xl font-extrabold text-[#4A2C2A] bg-gray-50 border border-gray-200 rounded-xl focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-100 transition-all duration-200 outline-none" />
                        <input type="text" maxlength="1" pattern="\d*" inputmode="numeric" class="w-12 h-14 text-center text-xl font-extrabold text-[#4A2C2A] bg-gray-50 border border-gray-200 rounded-xl focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-100 transition-all duration-200 outline-none" />
                        <input type="text" maxlength="1" pattern="\d*" inputmode="numeric" class="w-12 h-14 text-center text-xl font-extrabold text-[#4A2C2A] bg-gray-50 border border-gray-200 rounded-xl focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-100 transition-all duration-200 outline-none" />
                        <input type="text" maxlength="1" pattern="\d*" inputmode="numeric" class="w-12 h-14 text-center text-xl font-extrabold text-[#4A2C2A] bg-gray-50 border border-gray-200 rounded-xl focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-100 transition-all duration-200 outline-none" />
                        <input type="text" maxlength="1" pattern="\d*" inputmode="numeric" class="w-12 h-14 text-center text-xl font-extrabold text-[#4A2C2A] bg-gray-50 border border-gray-200 rounded-xl focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-100 transition-all duration-200 outline-none" />
                        <input type="text" maxlength="1" pattern="\d*" inputmode="numeric" class="w-12 h-14 text-center text-xl font-extrabold text-[#4A2C2A] bg-gray-50 border border-gray-200 rounded-xl focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-100 transition-all duration-200 outline-none" />
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#4A2C2A] hover:bg-orange-600 text-white py-3.5 rounded-2xl font-bold transition-all duration-300 shadow-md hover:shadow-orange-200 hover:shadow-lg active:scale-[0.99] uppercase tracking-widest mt-4">
                    Verifikasi Akun
                </button>
            </form>

            <form action="{{ route('verify.otp.resend') }}" method="POST" class="mt-6 text-center">
                @csrf
                <input type="hidden" name="email" value="{{ $email ?? '' }}">
                <p class="text-xs text-gray-400 font-medium">
                    Tidak menerima kode? 
                    <button type="submit" class="font-bold text-orange-600 hover:text-orange-700 transition ml-1 bg-transparent border-none cursor-pointer border-b border-dashed border-orange-400 hover:border-orange-600 pb-0.5">
                        Kirim Ulang OTP
                    </button>
                </p>
            </form>
        </div>
        
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = document.querySelectorAll('#otp-inputs-container input');
        const hiddenFinalInput = document.getElementById('otp_code');
        const form = document.getElementById('otp-form');

        inputs.forEach((input, index) => {
            // 1. Deteksi input angka: otomatis lompat ke kotak kanan berikutnya
            input.addEventListener('input', (e) => {
                // Pastikan hanya karakter angka saja yang diterima
                input.value = input.value.replace(/[^0-9]/g, '');
                
                if (input.value.length >= 1) {
                    if (index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                }
                combineOtpValues();
            });

            // 2. Deteksi backspace: otomatis mundur ke kotak kiri sebelumnya
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && input.value.length === 0) {
                    if (index > 0) {
                        inputs[index - 1].focus();
                    }
                }
            });
        });

        // Fungsi menggabungkan pecahan 6 kotak menjadi satu string utuh sebelum dikirim ke backend
        function combineOtpValues() {
            let combinedString = '';
            inputs.forEach(box => {
                combinedString += box.value;
            });
            hiddenFinalInput.value = combinedString;
        }

        // Mencegah submit jika form belum terisi penuh 6 angka
        form.addEventListener('submit', function (e) {
            combineOtpValues();
            if (hiddenFinalInput.value.length !== 6) {
                e.preventDefault();
                alert('Silakan lengkapi kode OTP 6-digit Anda.');
            }
        });
    });
</script>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.4s ease-out forwards;
    }
</style>
@endsection