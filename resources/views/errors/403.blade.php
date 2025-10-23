@extends('layouts.app')
@section('title','Akses Ditolak')
@section('page_title','Akses Ditolak')
@section('content')
  <div class="mp-card">
    <h3 style="margin:0 0 8px;">Kamu tidak memiliki akses</h3>
    <p>Menu ini hanya dapat diakses oleh pengguna dengan peran <b>Admin</b>.</p>
    <a href="{{ route('dashboard') }}" class="btn-primary btn-solid">Kembali ke Dashboard</a>
  </div>
@endsection