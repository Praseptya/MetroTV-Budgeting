<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MetroTV Budgeting')</title>

    {{-- CSS global --}}
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

    {{-- Font Awesome (ikon) --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    {{-- CSS khusus halaman --}}
    @stack('styles')
    @stack('head')
</head>
<body>
    <div class="dashboard-container">
        {{-- Sidebar --}}
        @include('partials.sidebar')

        {{-- Main --}}
        <main class="main-content">
            {{-- Header --}}
            @include('partials.header', ['title' => trim($__env->yieldContent('page_title', '')) ])

            {{-- Konten --}}
            <div class="page-content">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- Script global minimal --}}
    <script src="{{ asset('js/layout.js') }}" defer></script>
    <script src="{{ asset('js/sidebar.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    @stack('scripts')
</body>
</html>