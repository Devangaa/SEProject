@extends('layouts.app')

{{-- ============================================================================= --}}
{{-- FILE: admin/dashboard.blade.php --}}
{{-- HALAMAN: Dashboard Admin --}}
{{-- DESKRIPSI: Ringkasan statistik bisnis, grafik, dan aktivitas terbaru untuk admin. --}}
{{-- ============================================================================= --}}

@section('title', 'Dashboard Admin')

@section('content')
<div class="w-full">
    <div class="max-w-7xl mx-auto px-6">
        
        <div class="bg-gradient-to-r from-amber-700 to-amber-500 rounded-[2.5rem] p-10 shadow-xl shadow-amber-100 relative overflow-hidden">
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-10 -mb-10 w-40 h-40 bg-black/5 rounded-full blur-2xl"></div>
            
            <div class="relative z-10">
                <p class="text-amber-100 font-medium mb-2">Selamat datang kembali,</p>
                <h1 class="text-3xl md:text-4xl font-bold text-white tracking-tight">
                    {{ auth()->user()->username }}
                </h1>
            </div>
        </div>

        {{-- Bagian: Menu Admin --}}
        <div class="mt-12">
        <div class="mt-12">
            <div class="flex items-center justify-between mb-8">
                <div class="flex flex-col">
                    <h2 class="text-xl font-bold text-gray-900">Menu Admin</h2>
                    <p class="text-sm text-gray-400 mt-1">Kelola operasional harian tokomu di sini.</p>
                </div>
            </div>

            {{-- Bagian: Kartu Menu --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <a href="{{ route('admin.produk.index') }}" class="bg-white p-8 rounded-[2.5rem] border border-gray-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                    <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-500 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Kelola Produk</h3>
                    <p class="text-gray-400 text-xs leading-relaxed mb-6">Kelola katalog produk, stok, dan kategori barang.</p>
                </a>

                <a href="{{ route('admin.transaksi.index') }}" class="bg-white p-8 rounded-[2.5rem] border border-gray-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                    <div class="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-500 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Transaksi</h3>
                    <p class="text-gray-400 text-xs leading-relaxed mb-6">Pantau transaksi masuk dan kelola status pengiriman.</p>
                </a>

                <a href="{{ route('admin.keuangan.index') }}" class="bg-white p-8 rounded-[2.5rem] border border-gray-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                    <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-700 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Keuangan</h3>
                    <p class="text-gray-400 text-xs leading-relaxed mb-6">Catat dan pantau arus keuangan bisnismu secara terstruktur.</p>
                </a>

            </div>
        </div>
    </div>
</div>
@endsection
