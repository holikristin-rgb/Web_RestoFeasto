<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->username; // Form uses 'username' for email
        $password = $request->password;

        $validator = Validator::make(['email' => $email, 'password' => $password], [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Attempt login
        if (Auth::attempt([
            'email' => $email,
            'password' => $password
        ])) {
            $user = Auth::user();

            // Check verification status
            if (!$user->is_verified) {
                // Generate a new OTP code
                $otp_code = (string) rand(100000, 999999);
                $user->otp_code = $otp_code;
                $user->save();

                // Send/Log OTP
                try {
                    Mail::raw("RestoFeasto OTP Verification code is: {$otp_code}\nYou can verify at: " . route('verify.otp.view', ['email' => $user->email]), function($message) use ($user) {
                        $message->to($user->email)->subject('RestoFeasto - OTP Verification');
                    });
                } catch (\Exception $e) {
                    Log::error("Failed to send OTP email: " . $e->getMessage());
                }
                Log::info("Email OTP Verification sent to: {$user->email} | OTP Code: {$otp_code}");

                // Logout
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('verify.otp.view')
                    ->with('email', $email)
                    ->withErrors(['otp' => 'Akun Anda belum terverifikasi. Silakan masukkan kode OTP yang telah dikirim ke email Anda.']);
            }

            $request->session()->regenerate();

            // Redirect based on role
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'kasir') {
                return redirect()->route('kasir.dashboard');
            } else {
                return redirect()->route('beranda');
            }
        }

        return back()->withErrors(['username' => 'Email atau password salah.'])->withInput();
    }

    public function register(Request $request)
    {
        // Form inputs: name, email, phone, password
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'email' => 'required|string|email|max:150|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $otp_code = (string) rand(100000, 999999);

        // Create new user (role: pelanggan)
        $user = User::create([
            'nama' => $request->name,
            'email' => $request->email,
            'no_hp' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'pelanggan',
            'otp_code' => $otp_code,
            'is_verified' => false
        ]);

        // Log and Send Mail
        try {
            Mail::raw("Your OTP Verification code is: {$otp_code}\nYou can verify at: " . route('verify.otp.view', ['email' => $user->email]), function($message) use ($user) {
                $message->to($user->email)->subject('RestoFeasto - OTP Verification');
            });
        } catch (\Exception $e) {
            Log::error("Failed to send OTP email: " . $e->getMessage());
        }
        Log::info("Email OTP Verification sent to: {$user->email} | OTP Code: {$otp_code}");

        return redirect()->route('verify.otp.view')->with('email', $user->email)->with('success', 'Registrasi berhasil! Silakan masukkan kode OTP yang dikirim ke email Anda.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('index');
    }

    public function showVerifyOtpForm(Request $request)
    {
        $email = $request->email ?? session('email');
        return view('auth.verify-otp', compact('email'));
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('email', $request->email)
                    ->where('otp_code', $request->otp_code)
                    ->first();

        if (!$user) {
            return back()->withErrors(['otp_code' => 'Kode OTP yang dimasukkan tidak valid.'])->withInput();
        }

        $user->is_verified = true;
        $user->otp_code = null;
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('beranda')->with('success', 'Akun Anda berhasil diverifikasi!');
    }

    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = User::where('email', $request->email)->first();
        
        $otp_code = (string) rand(100000, 999999);
        $user->otp_code = $otp_code;
        $user->save();

        // Log and Send Mail
        try {
            Mail::raw("Your OTP Verification code is: {$otp_code}\nYou can verify at: " . route('verify.otp.view', ['email' => $user->email]), function($message) use ($user) {
                $message->to($user->email)->subject('RestoFeasto - OTP Verification');
            });
        } catch (\Exception $e) {
            Log::error("Failed to send OTP email: " . $e->getMessage());
        }
        Log::info("Email OTP Verification sent to: {$user->email} | OTP Code: {$otp_code}");

        return redirect()->route('verify.otp.view')->with('email', $request->email)->with('success', 'Kode OTP baru telah dikirim ke email Anda.');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );

        $link = route('password.reset', ['token' => $token, 'email' => $request->email]);

        try {
            Mail::raw("Reset your password by visiting this link: {$link}", function($message) use ($request) {
                $message->to($request->email)->subject('RestoFeasto - Reset Password');
            });
        } catch (\Exception $e) {
            Log::error("Failed to send Reset Password email: " . $e->getMessage());
        }
        Log::info("Reset Password Link sent to: {$request->email} | Link: {$link}");

        return back()->with('success', 'Link reset password telah dikirim ke email Anda.');
    }

    public function showResetPasswordForm(Request $request, $token)
    {
        $email = $request->email;
        return view('auth.reset-password', compact('token', 'email'));
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$reset) {
            return back()->withErrors(['email' => 'Token reset password tidak valid atau kedaluwarsa.'])->withInput();
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('index')->with('success', 'Password Anda berhasil diubah. Silakan login.');
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password berhasil diubah.');
    }
}
