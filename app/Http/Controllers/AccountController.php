<?php

namespace App\Http\Controllers;

use App\Mail\PasswordOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AccountController extends Controller
{
    public function passwordForm(Request $request)
    {
        return view('account.password', [
            'user' => $request->user(),
            'otpSent' => session('otp_sent', false),
            'resendAt' => session('otp_resend_at'),
            'flashEmail' => session('otp_email'),
            // tampilkan OTP di dev agar bisa ngetes kalau SMTP belum jalan
            'devOtp' => app()->environment('local') ? session('dev_last_otp') : null,
        ]);
    }

    public function sendOtp(Request $request)
    {
        $user = $request->user();
        $uid = $user->id_user ?? $user->id;
        $email = $user->email;

        if (! $email) {
            return back()->with('error', 'Email akun tidak ditemukan.');
        }

        // rate limit 60s
        $resendKey = "otp:pwd:resend:$uid";
        if (Cache::has($resendKey)) {
            return back()->with('error', 'Tunggu beberapa detik sebelum kirim ulang OTP.');
        }

        $code = (string) random_int(100000, 999999);
        $hash = hash('sha256', $code);

        // simpan ke cache, kedaluwarsa 10 menit
        Cache::put("otp:pwd:$uid", $hash, now()->addMinutes(10));
        Cache::put($resendKey, true, now()->addSeconds(60));

        try {
            Mail::to($email)->send(new PasswordOtpMail($user, $code));
        } catch (\Throwable $e) {
            Log::error('Gagal kirim OTP: '.$e->getMessage());
            // fallback dev: tampilkan OTP di layar kalau APP_ENV=local
            if (app()->environment('local')) {
                session(['dev_last_otp' => $code]);

                return back()->with('warn', 'SMTP belum siap. OTP ditampilkan di layar dev.');
            }

            return back()->with('error', 'Gagal mengirim email OTP. Hubungi admin.');
        }

        if (app()->environment('local')) {
            session(['dev_last_otp' => $code]); // biar gampang tes
        }

        session([
            'otp_sent' => true,
            'otp_email' => $email,
            'otp_resend_at' => now()->addSeconds(60)->toDateTimeString(),
        ]);

        return back()->with('success', 'Kode OTP telah dikirim ke email Anda.');
    }

    public function verifyAndUpdate(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ]);

        $user = $request->user();
        $uid = $user->id_user ?? $user->id;

        $cached = Cache::get("otp:pwd:$uid");
        if (! $cached) {
            return back()->with('error', 'OTP tidak ditemukan atau sudah kedaluwarsa.')->withInput();
        }

        $ok = hash_equals($cached, hash('sha256', $request->otp));
        if (! $ok) {
            return back()->with('error', 'Kode OTP tidak valid.')->withInput();
        }

        // valid → update password
        \DB::table('users')
            ->where('id_user', $uid)
            ->orWhere('id', $uid)
            ->update([
                'password' => Hash::make($request->password),
                'updated_at' => now(),
            ]);

        // bersihkan cache
        Cache::forget("otp:pwd:$uid");
        Cache::forget("otp:pwd:resend:$uid");

        // bersihkan session OTP
        $request->session()->forget(['otp_sent', 'otp_email', 'otp_resend_at', 'dev_last_otp']);

        return redirect()->route('help.index')->with('success', 'Password berhasil diperbarui.');
    }
}
