@extends('layouts.app')

@section('title','Help Center')
@section('page_title','Help Center')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/master-data.css') }}">
  <link rel="stylesheet" href="{{ asset('css/help-center.css') }}">
@endpush

@section('content')
  {{-- Flash (opsional) --}}
  @if(session('success') || session('error'))
    <div class="flash-wrap">
      @if(session('success'))
        <div class="flash success">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="flash error">{{ session('error') }}</div>
      @endif
    </div>
  @endif

  {{-- Grid 2 kolom: kiri = password + kontak, kanan = panduan + tautan cepat --}}
  <div class="grid-2">
    {{-- Ganti Password --}}
    <div class="mp-card">
      <h3 class="card-title">Ganti Password</h3>
      <p class="help-text">
        Anda bisa mengganti password akun sendiri melalui halaman reset password.
        Sistem akan mengirimkan kode OTP ke email Anda untuk verifikasi.
      </p>

      <ol class="help-steps">
        <li>Buka halaman <em>Reset Password</em>.</li>
        <li>Masukkan email, kemudian cek kode OTP yang dikirim.</li>
        <li>Masukkan OTP, lalu buat password baru.</li>
      </ol>

      <div class="row-gap">
        {{-- arahkan ke flow reset/OTP milik kita --}}
        <a class="btn-primary btn-solid" href="{{ url('account/password') }}">
          Ganti Password
        </a>
        <span class="help-note">Jika email OTP belum masuk, periksa folder Spam/Promotions.</span>
      </div>
    </div>

    {{-- Panduan Singkat --}}
    <div class="mp-card">
      <h3 class="card-title">Panduan Lengkap Penggunaan</h3>
      <details class="faq" open>
        <summary>Alur Kerja Singkat</summary>
        <ul class="bullet">
          <li>Siapkan data di <b>Master Data</b> (Item → Program → Template).</li>
          <li>Buat pengajuan di <b>Buat Budget Baru</b> dengan memilih Template.</li>
          <li>Lengkapi <b>Nama Budget</b>, <b>Departemen</b>, <b>Periode</b>, <b>Deskripsi</b>, lalu simpan.</li>
          <li>Edit item (ubah QTY, tambah/hapus item) di halaman <b>Detail/Edit Budget</b>.</li>
          <li>Atasan memproses di <b>Approval Budget</b> (Approve / Revisi / Ditolak).</li>
          <li>Pantau ringkasan di <b>Dashboard</b> & ekspor di <b>Report</b>.</li>
        </ul>
      </details>

      <details class="faq">
        <summary>1) Menyiapkan Master Data</summary>
        <h4 class="hc-h4">a. Master Item</h4>
        <ul class="bullet">
          <li>Buka <a href="{{ route('master.items.index') }}">Master Item</a> → isi <i>Nama Item</i>, <i>Unit</i>, <i>Bottom/Top Price</i>, <i>Deskripsi</i>.</li>
          <li>Kolom <b>Unit</b> mendukung pencarian. Jika nama unit belum ada, ketik manual → akan otomatis dibuat.</li>
          <li>Harga otomatis diformat ribuan saat ketik; simpan dengan tombol <b>Save</b>.</li>
        </ul>

        <h4 class="hc-h4">b. Master Program</h4>
        <ul class="bullet">
          <li>Buka <a href="{{ route('master.program.index') }}">Master Program</a> → isi <i>Nama Program</i>, pilih <b>PIC</b> (cari & klik dari daftar), pilih <b>Kategori</b> (On/Off Air), isi <i>Deskripsi</i>.</li>
          <li>Edit baris lewat ikon <i class="fa-solid fa-pen"></i>; simpan perubahan.</li>
        </ul>

        <h4 class="hc-h4">c. Master Template</h4>
        <ul class="bullet">
          <li>Buka <a href="{{ route('master.templates.index') }}">Master Template</a> → isi <i>Nama Template</i>, pilih <b>Event/Program</b> (PIC & kategori akan otomatis mengikuti), atur <b>Kategori</b> & <i>Deskripsi</i>.</li>
          <li>Tambahkan item dari Master via tombol <b>Add Item</b> → popup muncul → cari/klik item → otomatis masuk ke tabel.</li>
          <li>Atur QTY dengan tombol <span class="kbd">−</span>/<span class="kbd">＋</span>, hapus item via menu aksi.</li>
          <li>Grand total dihitung otomatis dari <code>qty × harga</code>.</li>
        </ul>
      </details>

      <details class="faq">
        <summary>2) Membuat Budget Baru</summary>
        <ul class="bullet">
          <li>Buka <a href="{{ route('budgets.create') }}">Buat Budget Baru</a>.</li>
          <li>Pilih <b>Template</b> → otomatis mengisi <b>Total Budget</b>, <b>Deskripsi</b>, <b>PIC</b>, <b>Kategori</b> (hanya tampil).</li>
          <li>Isi <b>Nama Budget</b> (wajib), <b>Departemen</b> (wajib), dan <b>Periode</b> (tanggal mulai & selesai, wajib).</li>
          <li>Klik <b>Save</b> untuk membuat budget. Sistem akan membuka halaman <b>Detail Budget</b> berisi daftar item dari template.</li>
        </ul>
      </details>

      <details class="faq">
        <summary>3) Edit Budget & Kelola Item</summary>
        <ul class="bullet">
          <li>Di halaman <b>Edit/Detail Budget</b>, ubah QTY dengan <span class="kbd">−</span>/<span class="kbd">＋</span> atau ketik manual, total akan diperbarui otomatis.</li>
          <li>Tambah item baru dari master: klik <b>Add Item</b> → popup daftar item (langsung tampil) → cari/klik <b>Tambah</b>.</li>
          <li>Jika item yang sama sudah ada, sistem menambah <b>QTY</b> (tidak menduplikasi baris).</li>
          <li>Hapus item via menu aksi. Simpan perubahan sebelum keluar.</li>
        </ul>
      </details>

      <details class="faq">
        <summary>4) Status Budget & Aturan Aksi</summary>
        <table class="hc-table">
          <thead>
            <tr>
              <th>Status</th>
              <th>Makna</th>
              <th>Aksi Pembuat</th>
              <th>Aksi Atasan</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="status pending"><i class="fas fa-circle"></i> Pending</span></td>
              <td>Menunggu persetujuan.</td>
              <td>Edit / Hapus / Lihat</td>
              <td>Approve / Revisi / Tolak</td>
            </tr>
            <tr>
              <td><span class="status revision"><i class="fas fa-circle"></i> Revisi</span></td>
              <td>Perlu perbaikan, lalu ajukan ulang.</td>
              <td>Edit lalu simpan</td>
              <td>—</td>
            </tr>
            <tr>
              <td><span class="status approved"><i class="fas fa-circle"></i> Approve</span></td>
              <td>Disetujui final.</td>
              <td>Lihat / Download (tanpa edit)</td>
              <td>—</td>
            </tr>
            <tr>
              <td><span class="status rejected"><i class="fas fa-circle"></i> Ditolak</span></td>
              <td>Pengajuan tidak disetujui.</td>
              <td>Lihat / Revisi (jika diizinkan) / Hapus</td>
              <td>—</td>
            </tr>
          </tbody>
        </table>
        <p class="help-note">Catatan: Tombol <b>Delete</b> hanya muncul jika status <i>bukan</i> Pending (sesuai aturan halaman).</p>
      </details>

      <details class="faq">
        <summary>5) Approval Budget (Atasan)</summary>
        <ul class="bullet">
          <li>Buka menu <b>Approval Budget</b> (hanya level manager/director/admin).</li>
          <li>Gunakan kolom pencarian/penyaring, lalu pilih aksi:
            <ul class="bullet">
              <li><b>Approve</b> → budget final, tidak bisa diedit pembuat.</li>
              <li><b>Revisi</b> → masukkan catatan revisi, pembuat memperbaiki lalu simpan.</li>
              <li><b>Reject</b> → tolak pengajuan (opsional beri alasan).</li>
            </ul>
          </li>
          <li>Setelah <b>Approve</b>, hanya tersedia <i>View Detail</i> & <i>Download</i>.</li>
        </ul>
      </details>

      <details class="faq">
        <summary>6) Dashboard & Report</summary>
        <ul class="bullet">
          <li><b>Dashboard</b> menampilkan:
            <ul class="bullet">
              <li>Stat card <i>Total Pengajuan</i>, <i>Disetujui</i>, <i>Menunggu</i>.</li>
              <li>Grafik <i>Laporan Pengajuan</i> (filter 12 bulan / 30 hari / 7 hari / 24 jam).</li>
              <li>Tabel <i>Pengajuan Terbaru</i> beserta status terakhir.</li>
            </ul>
          </li>
          <li><b>Report</b>: tabel detail pengajuan (Budget, Template, Dept, Periode, PIC, Status, Total) + tombol <b>Export</b> (CSV/Excel).</li>
        </ul>
      </details>

      <details class="faq">
        <summary>7) Hak Akses Singkat</summary>
        <ul class="bullet">
          <li><b>Admin</b>: semua fitur + Manajemen User.</li>
          <li><b>Manager/Director (atasan)</b>: semua kecuali pengelolaan user; memiliki menu <b>Approval Budget</b>.</li>
          <li><b>Staff</b>: tidak bisa edit/hapus Master Program/Item/Template, tidak melihat menu Manajemen User & Approval (kecuali diberi hak khusus).</li>
        </ul>
      </details>

      <details class="faq">
        <summary>8) Troubleshooting</summary>
        <ul class="bullet">
          <li><b>OTP tidak masuk</b>: cek Spam/Promotions, tunggu ±5 menit, gunakan tombol kirim ulang.</li>
          <li><b>PIC tidak muncul saat pilih program</b>: pastikan pilih dari daftar pencarian (bukan ketik manual).</li>
          <li><b>QTY tombol tidak bereaksi</b>: pastikan tidak dalam mode <i>read-only</i> (status Approved) dan simpan perubahan.</li>
          <li><b>Hak akses ditolak</b>: minta admin mengecek <i>user_level</i>.</li>
        </ul>
      </details>
    </div>

    {{-- Kontak Bantuan --}}
    <div class="mp-card">
      <h3 class="card-title">Kontak Bantuan</h3>
      <ul class="contact-list">
        <li>
          <i class="fa-solid fa-envelope"></i>
          <span>it-support@metrotv.com</span>
        </li>
        <li>
          <i class="fa-solid fa-phone"></i>
          <span>Ext: 1234</span>
        </li>
      </ul>
      <p class="help-note">
        Jam layanan: Senin–Jumat, 09.00–17.00.
      </p>
    </div>

    {{-- FAQ ringkas (opsional) --}}
    <div class="mp-card">
      <h3 class="card-title">FAQ</h3>
      <details class="faq">
        <summary>OTP tidak masuk, apa yang harus saya lakukan?</summary>
        <p>Cek folder Spam/Promotions. Jika masih belum ada setelah 60 detik, klik kirim ulang OTP di halaman reset.</p>
      </details>
      <details class="faq">
        <summary>Tidak bisa akses Approval Budget?</summary>
        <p>Menu ini hanya untuk level manager ke atas. Minta admin untuk menaikkan hak akses jika diperlukan.</p>
      </details>
    </div>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const resetId = @json($resetUserId ?? null);
  if (!resetId) return;

  // Contoh: tombol reset di setiap baris punya id="resetBtn-<id_user>"
  const trigger = document.getElementById('resetBtn-' + resetId);
  if (trigger) {
    trigger.click();   // buka modal reset secara otomatis
  }
});
</script>
@endpush