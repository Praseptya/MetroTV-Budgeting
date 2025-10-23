<!doctype html>
<html>
  <body style="font-family:Arial,Helvetica,sans-serif;">
    <p>Halo {{ $user->name ?? 'User' }},</p>
    <p>Kode OTP untuk mengganti password Anda:</p>
    <h2 style="letter-spacing:4px;">{{ $code }}</h2>
    <p>Kode ini berlaku selama 10 menit. Jangan bagikan ke siapa pun.</p>
    <p>Terima kasih.</p>
  </body>
</html>