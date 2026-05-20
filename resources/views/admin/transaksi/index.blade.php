@extends('layouts.app')

{{-- ============================================================================= --}}
{{-- FILE: admin/transaksi/index.blade.php --}}
{{-- HALAMAN: Kelola Transaksi --}}
{{-- DESKRIPSI: Daftar semua transaksi dengan filter status dan pencarian. --}}
{{-- ============================================================================= --}}

@section('title', 'Kelola Transaksi')

@section('content')
{{-- Bagian: Kontainer Halaman --}}
<div class="w-full min-h-screen bg-gray-50/50 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Bagian: Breadcrumb --}}
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-gray-400 hover:text-green-600 transition-colors flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Dashboard
                    </a>
                </li>
            </ol>
        </nav>

        <div data-aos="fade-right" class="mb-8">
            <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full uppercase">
                Kelola Transaksi
            </span>
            <h1 class="text-3xl font-extrabold text-gray-900 mt-4 tracking-tight">Kelola Transaksi</h1>
            <p class="text-gray-500 text-sm mt-2 font-medium">Pantau dan kelola semua pesanan pelanggan di sini</p>
        </div>

        <div data-aos="fade-up">
        {{-- Tabs --}}
            <div class="flex gap-4 mb-6 border-b border-gray-200 overflow-x-auto no-scrollbar">
                <a href="{{ route('admin.transaksi.index', ['tab' => 'menunggu-pembayaran']) }}" 
                class="px-4 py-3 font-bold text-sm whitespace-nowrap transition-colors border-b-2 {{ $tab == 'menunggu-pembayaran' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-green-600' }}">
                    Menunggu Pembayaran
                </a>
                <a href="{{ route('admin.transaksi.index', ['tab' => 'diproses']) }}" 
                class="px-4 py-3 font-bold text-sm whitespace-nowrap transition-colors border-b-2 {{ $tab == 'diproses' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-green-600' }}">
                    Diproses
                </a>
                <a href="{{ route('admin.transaksi.index', ['tab' => 'riwayat']) }}" 
                class="px-4 py-3 font-bold text-sm whitespace-nowrap transition-colors border-b-2 {{ $tab == 'riwayat' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-green-600' }}">
                    Riwayat
                </a>
            </div>

            <div class="space-y-6">
                @forelse($transaksis as $transaksi)
                    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                        {{-- Header Transaksi --}}
                        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gray-50/50">
                            <div class="flex items-center gap-4">
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-400 font-bold uppercase">Pelanggan</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $transaksi->user->username }}</span>
                                </div>
                                <div class="w-px h-8 bg-gray-200 hidden md:block"></div>
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-400 font-bold uppercase">ID Order</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $transaksi->order_id }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-4 py-1.5 text-xs font-bold rounded-xl uppercase tracking-wider
                                    @if($transaksi->status == 'Menunggu Pembayaran') bg-yellow-100 text-yellow-700
                                    @elseif($transaksi->status == 'Menunggu Konfirmasi') bg-yellow-100 text-yellow-700
                                    @elseif($transaksi->status == 'Diproses') bg-blue-100 text-blue-700
                                    @elseif($transaksi->status == 'Dikirim') bg-purple-100 text-purple-700
                                    @elseif($transaksi->status == 'Selesai') bg-green-100 text-green-700
                                    @elseif($transaksi->status == 'Dibatalkan') bg-red-100 text-red-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ $transaksi->status }}
                                </span>
                                <span class="px-4 py-1.5 text-xs font-bold rounded-xl uppercase tracking-wider bg-gray-100 text-gray-600">
                                    {{ strtoupper($transaksi->metode_pembayaran) }}
                                </span>
                            </div>
                        </div>

                        {{-- Body Transaksi (Daftar Produk) --}}
                        <div class="p-6 space-y-4">
                            @foreach($transaksi->detailProduks as $detail)
                                @php
                                    $foto = $detail->produk->foto_produk;
                                    if (is_array($foto)) {
                                        $foto = $foto[0] ?? null;
                                    } elseif (is_string($foto)) {
                                        $decodedFoto = json_decode($foto, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedFoto)) {
                                            $foto = $decodedFoto[0] ?? $foto;
                                        }
                                    }
                                    $photoUrl = $foto ? asset("uploads/produk/" . $foto) : 'https://ui-avatars.com/api/?name=' . urlencode($detail->produk->nama_produk);
                                @endphp
                                <div class="flex items-center gap-4">
                                    <img src="{{ $photoUrl }}" alt="{{ $detail->produk->nama_produk }}" class="w-16 h-16 object-cover rounded-2xl border border-gray-100 flex-shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-base font-bold text-gray-900 truncate">{{ $detail->produk->nama_produk }}</h4>
                                        <p class="text-sm text-gray-500 mt-0.5">
                                            {{ $detail->jumlah }} {{ $detail->produk->unit ?? 'pcs' }} x Rp{{ number_format($detail->produk->harga, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <div class="text-right hidden md:block">
                                        <p class="text-xs text-gray-400 font-bold uppercase mb-1">Subtotal</p>
                                        <p class="text-sm font-black text-gray-900">Rp{{ number_format($detail->total_harga, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            @endforeach

                            @foreach($transaksi->detailLayanans as $detail)
                                @php
                                    $foto = $detail->layanan->foto_layanan;
                                    if (is_array($foto)) {
                                        $foto = $foto[0] ?? null;
                                    } elseif (is_string($foto)) {
                                        $decodedFoto = json_decode($foto, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedFoto)) {
                                            $foto = $decodedFoto[0] ?? $foto;
                                        }
                                    }
                                    $photoUrl = $foto ? asset("uploads/layanan/" . $foto) : 'https://ui-avatars.com/api/?name=' . urlencode($detail->layanan->nama_layanan);
                                @endphp
                                <div class="flex items-center gap-4">
                                    <img src="{{ $photoUrl }}" alt="{{ $detail->layanan->nama_layanan }}" class="w-16 h-16 object-cover rounded-2xl border border-gray-100 flex-shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-base font-bold text-gray-900 truncate">{{ $detail->layanan->nama_layanan }}</h4>
                                        <p class="text-sm text-gray-500 mt-0.5">
                                            Layanan x Rp{{ number_format($detail->layanan->harga, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <div class="text-right hidden md:block">
                                        <p class="text-xs text-gray-400 font-bold uppercase mb-1">Subtotal</p>
                                        <p class="text-sm font-black text-gray-900">Rp{{ number_format($detail->total_harga, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Footer Transaksi --}}
                        <div class="p-6 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
                            <div class="w-full md:w-auto text-center md:text-left">
                                <p class="text-xs text-gray-400 font-bold uppercase mb-1">Total Pembayaran</p>
                                <p class="text-xl font-black text-green-600">Rp{{ number_format($transaksi->total_harga, 0, ',', '.') }}</p>
                            </div>
                            <div class="flex flex-col md:flex-row items-center gap-4 w-full md:w-auto">
                                <div class="text-xs text-gray-400 font-medium text-center md:text-right">
                                    {{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->locale('id')->translatedFormat('d M Y, H:i') }} WIB
                                </div>
                                <a href="{{ route('admin.transaksi.show', $transaksi->order_id) }}" class="w-full md:w-auto px-6 py-3 bg-green-600 text-white font-black rounded-xl hover:bg-green-700 transition text-sm text-center shadow-lg shadow-green-100">
                                    Kelola Pesanan
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-20 bg-white rounded-[2rem] border border-dashed border-gray-200">
                        <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-bold text-gray-900">Tidak ada transaksi</h3>
                        <p class="mt-2 text-gray-500 font-medium text-sm">Belum ada pesanan dengan status ini.</p>
                    </div>
                @endforelse

                <div class="mt-8">
                    {{ $transaksis->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
