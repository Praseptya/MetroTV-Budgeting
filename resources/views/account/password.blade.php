{{-- resources/views/account/password.blade.php --}}
@extends('layouts.app')

@section('title','Ganti Password')
@section('page_title','Ganti Password')

@push('styles')
<style>
/* satu properti per baris */
.pw-card { background: #ffffff; border-radius: 12px; padding: 16px; box-shadow: 0 1px 2px rgba(15,23,42,.06); max-width: 720px; }
.pw-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 900px) { .pw-row { grid-template-columns: 1fr; } }
.note { font-size: 13px; color: #64748b; }
</style>
@endpush

@section('content')
  @if(session('success')) <div class="flash success">{{ session('success') }}</div> @endif
  @if(session('error'))   <div class="flash error">{{ session('error') }}</div>   @endif

    @if(session('warn'))
    <div class="flash warn">{{ session('warn') }}</div>
    @endif
    @if(app()->environment('local') && !empty($devOtp))
    <div class="flash warn">OTP (dev): <b>{{ $devOtp }}</b></div>
    @endif


  <div class="pw-card">
    <form method="post" action="{{ route('account.password.send') }}" style="margin-bottom: 12px;">
      @csrf
      <label class="lbl">Email</label>
      <input class="inp" type="email" value="{{ $user->email }}" readonly>
      <div class="row-gap" style="margin-top:12px;">
        <button class="btn-outline" type="submit"
                {{ session('otp_resend_at') && now()->lt(\Carbon\Carbon::parse($resendAt)) ? 'disabled' : '' }}>
          {{ session('otp_sent') ? 'Kirim Ulang OTP' : 'Kirim OTP' }}
        </button>
        @if($resendAt && now()->lt(\Carbon\Carbon::parse($resendAt)))
          <span class="note">Anda bisa kirim ulang setelah {{ \Carbon\Carbon::parse($resendAt)->diffForHumans() }}.</span>
        @endif
      </div>
    </form>

    @if ($otpSent)
      <form method="post" action="{{ route('account.password.verify') }}">
        @csrf
        <div class="pw-row">
          <div>
            <label class="lbl">Kode OTP</label>
            <input class="inp" name="otp" maxlength="6" placeholder="Masukkan 6 digit" value="{{ old('otp') }}">
            @error('otp') <div class="flash error" style="margin-top:6px">{{ $message }}</div> @enderror
          </div>
          <div>
            <label class="lbl">Password Baru</label>
            <input class="inp" type="password" name="password" placeholder="Minimal 8 karakter">
            @error('password') <div class="flash error" style="margin-top:6px">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="pw-row" style="margin-top:12px;">
          <div>
            <label class="lbl">Konfirmasi Password Baru</label>
            <input class="inp" type="password" name="password_confirmation" placeholder="Ulangi password baru">
          </div>
        </div>

        <div class="row-gap" style="margin-top:16px;">
          <button class="btn-primary" type="submit">
            Simpan Password Baru
          </button>
        </div>
      </form>
    @else
      <div class="note">Klik <b>Kirim OTP</b> untuk menerima kode verifikasi di email Anda.</div>
    @endif
  </div>
@endsection
