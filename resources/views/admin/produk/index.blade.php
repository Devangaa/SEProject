@extends('layouts.app')

{{-- ============================================================================= --}}
{{-- FILE: admin/produk/index.blade.php --}}
{{-- HALAMAN: Kelola Produk --}}
{{-- DESKRIPSI: Tabel manajemen produk dengan CRUD, filter, dan modal terkait. --}}
{{-- ============================================================================= --}}

@section('title', 'Kelola Produk')

@section('content')
<div x-data="{ 
    showDeleteModal: false, 
    showReviewModal: false,
    reviewData: [],
    deleteUrl: '',
    showCreateModal: {{ ($errors->any() && !session('editingProductId')) ? 'true' : 'false' }}, 
    showEditModal: {{ session('editingProductId') ? 'true' : 'false' }},
    editUrl: '{{ session('editingProductId') ? route('admin.produk.update', session('editingProductId')) : '' }}',
    editData: {
        nama_produk: '{{ old('nama_produk', '') }}' || '',
        kategori: '{{ old('kategori', '') }}' || '',
        harga: '{{ old('harga', '') }}' || '',
        jumlah_stok: '{{ old('jumlah_stok', '') }}' || '',
        unit: '{{ old('unit', '') }}' || '',
        berat: '{{ old('berat', '') }}' || '',
        deskripsi: '{{ addslashes(old('deskripsi', '')) }}' || '',
        foto_produk: [],
    }}"
    x-init="
    @if($errors->any() && session('editingProductId'))
        @php
            $oldProduct = \App\Models\Product::find(session('editingProductId'));
        @endphp
        @if($oldProduct)
            editUrl = '{{ route('admin.produk.update', $oldProduct->id) }}';
            editData = {
                nama_produk: '{{ old('nama_produk', $oldProduct->nama_produk) }}',
                kategori: '{{ old('kategori', $oldProduct->kategori) }}',
                harga: '{{ old('harga', $oldProduct->harga) }}',
                jumlah_stok: '{{ old('jumlah_stok', $oldProduct->jumlah_stok) }}',
                unit: '{{ old('unit', $oldProduct->unit) }}',
                berat: '{{ old('berat', $oldProduct->berat) }}',
                deskripsi: '{{ addslashes(old('deskripsi', $oldProduct->deskripsi)) }}',
                foto_produk: {{ json_encode($oldProduct->foto_produk ?? []) }}
            };
        @endif
    @endif"
    class="w-full">

    <div class="min-h-screen bg-gray-50/50 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Bagian: Breadcrumb --}}
            <nav class="flex mb-8" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-gray-400 hover:text-amber-700 transition-colors flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Kembali ke Dashboard
                        </a>
                    </li>
                </ol>
            </nav>

            <div data-aos="fade-right">
                {{-- Bagian: Header & Tombol Tambah --}}
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                    <div>
                        <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full uppercase">
                            Manajemen Produk
                        </span>
                        <h1 class="text-3xl font-extrabold text-gray-900 mt-2">Data Produk</h1>
                        <p class="text-gray-500 text-sm">Kelola semua produk hidroponik yang tersedia di toko</p>
                    </div>
                    <button @click="showCreateModal = true" type="button" class="inline-flex items-center justify-center bg-amber-700 hover:bg-amber-800 text-white font-bold py-3 px-6 rounded-2xl transition-all shadow-lg shadow-amber-200 gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Tambah Produk
                    </button>
                </div>

                {{-- Bagian: Kartu Statistik --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    @php
                        $cardStyles = [
                            ['label' => 'Total Produk', 'value' => $stats['total'], 'color' => 'text-gray-900'],
                            ['label' => 'Produk Aktif', 'value' => $stats['aktif'], 'color' => 'text-amber-700'],
                            ['label' => 'Stok Menipis', 'value' => $stats['menipis'], 'color' => 'text-orange-500'],
                            ['label' => 'Dihapus', 'value' => $stats['dihapus'], 'color' => 'text-red-500'],
                        ];
                    @endphp
                    @foreach($cardStyles as $card)
                    <div class="bg-white p-6 rounded-[1.5rem] border border-gray-100 shadow-sm">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">{{ $card['label'] }}</p>
                        <p class="text-3xl font-black {{ $card['color'] }}">{{ $card['value'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div data-aos="fade-up">
                {{-- Bagian: Filter & Pencarian --}}
                <div class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm mb-6 relative overflow-visible">
                    <form action="{{ route('admin.produk.index') }}" method="GET" class="flex flex-col gap-4 sm:flex-row sm:flex-wrap items-start justify-between">
                        <div class="flex flex-1 items-center gap-2 min-w-0">
                            <div class="relative flex-1 min-w-0">
                                <span class="absolute inset-y-0 left-4 flex items-center text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </span>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama produk..." 
                                    class="w-full pl-12 pr-12 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-amber-500 transition outline-none text-sm">
                            </div>

                            @if(request('search') || request('category') || request('status'))
                                <a href="{{ route('admin.produk.index') }}" 
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all duration-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            @endif
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                            <div x-data="{ openCategory: false }" class="w-full sm:w-auto relative z-20">
                                <button type="button" @click="openCategory = !openCategory" class="w-full sm:w-auto bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold text-gray-600 outline-none focus:ring-2 focus:ring-amber-500 transition flex items-center justify-between hover:bg-gray-100">
                                    <span>{{ request('category') ? request('category') : 'Semua Kategori' }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transition-transform" :class="openCategory ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div
                                    x-show="openCategory"
                                    x-cloak
                                    @click.away="openCategory = false"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 -translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-2"
                                    class="absolute top-full left-0 right-0 sm:right-auto mt-2 bg-white border border-gray-100 rounded-xl shadow-xl z-[9999] overflow-hidden min-w-[160px]"
                                >
                                    <button type="button" @click="openCategory = false; document.querySelector('input[name=category]').value = ''; setTimeout(() => document.querySelector('form').submit(), 100)" class="w-full text-left px-4 py-3 text-sm font-bold text-gray-600 hover:bg-amber-50 hover:text-amber-700 transition {{ !request('category') ? 'bg-amber-50 text-amber-700' : '' }}">
                                        Semua Kategori
                                    </button>
                                    @foreach($categories as $cat)
                                        <button type="button" @click="openCategory = false; document.querySelector('input[name=category]').value = '{{ $cat }}'; setTimeout(() => document.querySelector('form').submit(), 100)" class="w-full text-left px-4 py-3 text-sm font-bold text-gray-600 hover:bg-amber-50 hover:text-amber-700 transition {{ request('category') == $cat ? 'bg-amber-50 text-amber-700' : '' }}">
                                            {{ $cat }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <input type="hidden" name="category" value="{{ request('category') ?? '' }}">
                            
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif

                            <button type="submit" class="hidden">Cari</button>

                            <a href="{{ route('admin.produk.index') }}" 
                            class="w-full sm:w-auto px-4 py-3 font-bold rounded-xl text-sm text-center transition-all duration-300 {{ request('status') != 'terhapus' ? 'bg-amber-700 text-white' : 'bg-gray-50 text-gray-400 hover:bg-gray-100' }}">
                                Aktif
                            </a>
                            <a href="{{ route('admin.produk.index', ['status' => 'terhapus']) }}" 
                            class="w-full sm:w-auto px-4 py-3 font-bold rounded-xl text-sm text-center transition-all duration-300 {{ request('status') == 'terhapus' ? 'bg-red-600 text-white' : 'bg-gray-50 text-gray-400 hover:bg-gray-100' }}">
                                Terhapus
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Bagian: Tabel Data --}}
                <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                        <h2 class="font-bold text-gray-900">Daftar Produk {{ request('status') == 'terhapus' ? '(Terhapus)' : '' }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'nama_produk', 'order' => request('sort_by') == 'nama_produk' && request('order') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-amber-700 transition-colors">
                                            Produk
                                            @if(request('sort_by') == 'nama_produk')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 {{ request('order') == 'asc' ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Kategori</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'harga', 'order' => request('sort_by') == 'harga' && request('order') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-amber-700 transition-colors">
                                            Harga
                                            @if(request('sort_by') == 'harga')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 {{ request('order') == 'asc' ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'jumlah_stok', 'order' => request('sort_by') == 'jumlah_stok' && request('order') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-amber-700 transition-colors">
                                            Stok
                                            @if(request('sort_by') == 'jumlah_stok')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 {{ request('order') == 'asc' ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'total_terjual', 'order' => request('sort_by') == 'total_terjual' && request('order') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-amber-700 transition-colors">
                                            Terjual
                                            @if(request('sort_by') == 'total_terjual')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 {{ request('order') == 'asc' ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'ulasans_avg_rating', 'order' => request('sort_by') == 'ulasans_avg_rating' && request('order') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-amber-700 transition-colors">
                                            Rating
                                            @if(request('sort_by') == 'ulasans_avg_rating')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 {{ request('order') == 'asc' ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Unit</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($products as $product)
                                <tr class="hover:bg-gray-50/30 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 aspect-square shrink-0 bg-gray-100 rounded-xl overflow-hidden border border-gray-100">
                                                <img src="{{ !empty($product->foto_produk) && is_array($product->foto_produk) 
                                                            ? asset('uploads/produk/' . $product->foto_produk[0]) 
                                                            : 'https://ui-avatars.com/api/?name=' . $product->nama_produk }}" 
                                                    class="w-full h-full rounded-lg object-cover">
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900 text-sm leading-tight">{{ $product->nama_produk }}</p>
                                                <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase">ID: DP0{{ $product->id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 bg-amber-50 text-amber-700 text-[10px] font-bold rounded-lg uppercase">{{ $product->kategori }}</span>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-900 text-sm whitespace-nowrap">
                                        Rp {{ number_format($product->harga, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-900 text-sm">{{ $product->jumlah_stok }}</td>
                                    <td class="px-6 py-4 font-bold text-gray-900 text-sm">{{ $product->total_terjual ?? 0 }}</td>
                                    <td class="px-6 py-4 font-bold text-gray-900 text-sm">
                                        <div class="flex items-center gap-1 text-orange-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <span>{{ number_format($product->ulasans_avg_rating ?? 0, 1) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 text-sm">{{ $product->unit }}</td>
                                    <td class="px-6 py-4">
                                        @if($product->is_delete)
                                            <span class="px-3 py-1 bg-red-50 text-red-600 text-[10px] font-bold rounded-lg uppercase">Terhapus</span>
                                        @elseif($product->jumlah_stok == 0)
                                            <span class="px-3 py-1 bg-red-50 text-red-600 text-[10px] font-bold rounded-lg uppercase">Stok Habis</span>
                                        @elseif($product->jumlah_stok <= 10)
                                            <span class="px-3 py-1 bg-orange-50 text-orange-600 text-[10px] font-bold rounded-lg uppercase">Stok Menipis</span>
                                        @else
                                            <span class="px-3 py-1 bg-amber-50 text-amber-700 text-[10px] font-bold rounded-lg uppercase">Aktif</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            @if($product->is_delete)
                                                <form action="{{ route('admin.produk.update', $product->id) }}" method="POST" class="inline">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="restore" value="1">
                                                    <button type="submit" class="inline-flex items-center justify-center h-10 w-10 rounded-xl bg-orange-50 text-orange-600 hover:bg-orange-600 hover:text-white transition-all" title="Pulihkan">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @else
                                                <button 
                                                    @click="
                                                        showReviewModal = true;
                                                        reviewData = {{ json_encode($product->ulasans) }};
                                                    "
                                                    class="inline-flex items-center justify-center h-10 w-10 rounded-xl bg-blue-50 text-blue-500 hover:bg-blue-500 hover:text-white transition-all" title="Lihat Ulasan">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2h-3l-4 4z" />
                                                    </svg>
                                                </button>
                                                
                                                <button 
                                                    @click="
                                                        showEditModal = true; 
                                                        editUrl = '{{ route('admin.produk.update', $product->id) }}';
                                                        editData = {
                                                            nama_produk: '{{ addslashes($product->nama_produk) }}',
                                                            kategori: '{{ $product->kategori }}',
                                                            harga: '{{ $product->harga }}',
                                                            jumlah_stok: '{{ $product->jumlah_stok }}',
                                                            unit: '{{ $product->unit }}',
                                                            berat: '{{ $product->berat ?? '' }}',
                                                            deskripsi: '{{ addslashes($product->deskripsi ?? '') }}',
                                                            foto_produk: {{ json_encode($product->foto_produk) }}
                                                        }
                                                    "
                                                    class="inline-flex items-center justify-center h-10 w-10 rounded-xl bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white transition-all" title="Edit">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>

                                                <button @click="showDeleteModal = true; deleteUrl = '{{ route('admin.produk.destroy', $product->id) }}'" 
                                                    type="button" 
                                                    class="inline-flex items-center justify-center h-10 w-10 rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all" title="Hapus">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-400 font-bold">Tidak ada data produk ditemukan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="p-6 bg-gray-50/50 flex justify-between items-center">
                        <p class="text-xs font-bold text-gray-400">
                            Menampilkan {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} produk
                        </p>
                        
                        <div class="flex gap-1">
                            @if ($products->onFirstPage())
                                <span class="px-4 py-2 bg-white border border-gray-100 text-gray-300 text-xs font-bold rounded-xl cursor-not-allowed">
                                    <
                                </span>
                            @else
                                <a href="{{ $products->previousPageUrl() }}" class="px-4 py-2 bg-white border border-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-amber-700 hover:text-white hover:border-amber-700 transition-all duration-300">
                                    <
                                </a>
                            @endif

                            @foreach ($products->getUrlRange(max(1, $products->currentPage() - 1), min($products->lastPage(), $products->currentPage() + 1)) as $page => $url)
                                @if ($page == $products->currentPage())
                                    <span class="px-4 py-2 bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm shadow-amber-100">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="px-4 py-2 bg-white border border-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-50 transition-all">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach

                            @if ($products->hasMorePages())
                                <a href="{{ $products->nextPageUrl() }}" class="px-4 py-2 bg-white border border-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-amber-700 hover:text-white hover:border-amber-700 transition-all duration-300">
                                    >
                                </a>
                            @else
                                <span class="px-4 py-2 bg-white border border-gray-100 text-gray-300 text-xs font-bold rounded-xl cursor-not-allowed">
                                    >
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Bagian: Modal Terkait --}}
    @include('admin.produk.modal-delete')
    @include('admin.produk.modal-create')
    @include('admin.produk.modal-edit')
    @include('admin.produk.modal-reviews')
</div>
@endsection
