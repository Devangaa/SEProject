@extends('layouts.app')

{{-- ============================================================================= --}}
{{-- FILE: admin/keuangan/index.blade.php --}}
{{-- HALAMAN: Kelola Keuangan --}}
{{-- DESKRIPSI: Tabel manajemen keuangan dengan CRUD, filter per bulan, dan modal terkait. --}}
{{-- ============================================================================= --}}

@section('title', 'Kelola Keuangan')

@section('content')
{{-- 
    ============================================================================
    SCRIPT: Alpine Component untuk Halaman Keuangan
    ============================================================================
    Logika modal management, filter bulan/tahun, calendar picker, dan form handling.
    File terstruktur: resources/js/admin/keuangan.js (definisi function keuanganComponent)
    ============================================================================
--}}
@push('scripts')
    <script>
        // ===== Data dari PHP =====
        window.keuanganData = {
            bulan: {{ $bulan }},
            tahun: {{ $tahun }},
            pendapatan: {{ $pendapatan }},
            pengeluaran: {{ $pengeluaran }},
            totalKas: {{ $totalKas }},
            hasErrors: {{ $errors->any() ? 'true' : 'false' }},
            routeUrl: '{{ route('admin.keuangan.index') }}'
        };
        
        // ===== Function: keuanganComponent =====
        // Return Alpine component object dengan state dan methods
        window.keuanganComponent = function() {
            const phpData = window.keuanganData;
            
            return {
                // ===== State: Modal Control =====
                showDeleteModal: false,
                deleteUrl: '',
                showCreateModal: phpData.hasErrors || false,
                showEditModal: false,
                editUrl: '',

                // ===== State: Filter & Navigation =====
                openBulan: false,
                openTahun: false,
                currentBulan: phpData.bulan,
                currentTahun: phpData.tahun,

                // ===== State: Edit Form Data =====
                editData: {
                    keterangan: '',
                    nominal: 0,
                    tipe_laporan: 'pengeluaran',
                    tanggal: '',
                },

                // ===== State: Edit Calendar =====
                currentMonthEdit: new Date().getMonth() + 1,
                currentYearEdit: new Date().getFullYear(),
                showCalendarEdit: false,

                // ===== State: Statistics =====
                pendapatan: phpData.pendapatan,
                pengeluaran: phpData.pengeluaran,
                totalKas: phpData.totalKas,

                // ===== Computed: Calendar Days for Edit Modal =====
                get calendarDaysEdit() {
                    const days = [];
                    const firstDay = new Date(this.currentYearEdit, this.currentMonthEdit - 1, 1).getDay();
                    const daysInMonth = new Date(this.currentYearEdit, this.currentMonthEdit, 0).getDate();
                    for (let i = 0; i < firstDay; i++) days.push(null);
                    for (let i = 1; i <= daysInMonth; i++) days.push(i);
                    return days;
                },

                // ===== Constants: Month & Day Names =====
                monthNamesEdit: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                dayNamesEdit: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],

                // ===== Methods: Calendar Management =====
                initializeEditCalendar() {
                    if (this.editData.tanggal) {
                        const [year, month, day] = this.editData.tanggal.split('-').map(Number);
                        this.currentYearEdit = year;
                        this.currentMonthEdit = month;
                    }
                },

                selectDateEdit(day) {
                    if (day) {
                        const date = new Date(this.currentYearEdit, this.currentMonthEdit - 1, day);
                        this.editData.tanggal = date.toISOString().split('T')[0];
                        this.showCalendarEdit = false;
                    }
                },

                prevMonthEdit() {
                    this.currentMonthEdit--;
                    if (this.currentMonthEdit < 1) {
                        this.currentMonthEdit = 12;
                        this.currentYearEdit--;
                    }
                },

                nextMonthEdit() {
                    this.currentMonthEdit++;
                    if (this.currentMonthEdit > 12) {
                        this.currentMonthEdit = 1;
                        this.currentYearEdit++;
                    }
                },

                // ===== Methods: Navigation =====
                navigateTo(bulan, tahun) {
                    window.location.href = phpData.routeUrl + `?bulan=${bulan}&tahun=${tahun}`;
                },

                // ===== Lifecycle =====
                init() {
                    window.alpineComponent = this;
                    if (phpData.hasErrors) {
                        this.showCreateModal = true;
                    }
                },
            };
        };
        
        // ===== Function: openEditModal =====
        // Membuka modal edit dengan data keuangan yang dipilih
        window.openEditModal = function(id, updateUrl, keterangan, nominal, tipe_laporan, tanggal) {
            if (!window.alpineComponent) return;

            window.alpineComponent.showEditModal = true;
            window.alpineComponent.editUrl = updateUrl;
            window.alpineComponent.editData = {
                keterangan: keterangan,
                nominal: parseFloat(nominal),
                tipe_laporan: tipe_laporan,
                tanggal: tanggal
            };

            // Initialize calendar setelah data diset
            setTimeout(() => {
                if (window.alpineComponent && typeof window.alpineComponent.initializeEditCalendar === 'function') {
                    window.alpineComponent.initializeEditCalendar();
                }
            }, 50);
        };
    </script>
@endpush

<div x-data="keuanganComponent()" x-init="init()" class="w-full">

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
                            Manajemen Keuangan
                        </span>
                        <h1 class="text-3xl font-extrabold text-gray-900 mt-2">Data Keuangan</h1>
                        <p class="text-gray-500 text-sm">Kelola data keuangan dan laporan pendapatan pengeluaran</p>
                    </div>
                    <button @click="showCreateModal = true" type="button" class="inline-flex items-center justify-center bg-amber-700 hover:bg-amber-800 text-white font-bold py-3 px-6 rounded-2xl transition-all shadow-lg shadow-amber-200 gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Tambah Laporan Keuangan
                    </button>
                </div>

                {{-- Bagian: Kartu Statistik --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-[1.5rem] border border-gray-100 shadow-sm">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Kas</p>
                        <p class="text-3xl font-black text-gray-900" x-text="`Rp ${new Intl.NumberFormat('id-ID').format(totalKas)}`"></p>
                    </div>
                    <div class="bg-white p-6 rounded-[1.5rem] border border-gray-100 shadow-sm">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Pendapatan</p>
                        <p class="text-3xl font-black text-amber-700" x-text="`Rp ${new Intl.NumberFormat('id-ID').format(pendapatan)}`"></p>
                    </div>
                    <div class="bg-white p-6 rounded-[1.5rem] border border-gray-100 shadow-sm">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Pengeluaran</p>
                        <p class="text-3xl font-black text-red-600" x-text="`Rp ${new Intl.NumberFormat('id-ID').format(pengeluaran)}`"></p>
                    </div>
                </div>
            </div>

            <div data-aos="fade-up">
                {{-- Bagian: Filter & Navigasi Bulan --}}
                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm mb-6">
                    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            {{-- Tombol Previous --}}
                            <button type="button" @click="
                                let newBulan = currentBulan - 1;
                                let newTahun = currentTahun;
                                if (newBulan < 1) {
                                    newBulan = 12;
                                    newTahun--;
                                }
                                navigateTo(newBulan, newTahun);
                            " class="p-3 bg-gray-100 hover:bg-amber-700 text-gray-600 hover:text-white rounded-xl transition-all flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            {{-- Bulan Dropdown --}}
                            <div class="flex-1 sm:flex-none relative">
                                <button type="button" @click="openBulan = !openBulan" class="w-full sm:w-auto bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold text-gray-600 outline-none focus:ring-2 focus:ring-amber-500 transition flex items-center justify-between hover:bg-gray-100 min-w-[140px]">
                                    <span x-text="['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][currentBulan - 1]"></span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transition-transform" :class="openBulan ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div
                                    x-show="openBulan"
                                    x-cloak
                                    @click.away="openBulan = false"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 -translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-2"
                                    class="absolute top-full left-0 mt-2 bg-white border border-gray-100 rounded-xl shadow-xl z-[9999] overflow-y-auto max-h-80 min-w-[140px]"
                                >
                                    @php
                                        $bulanNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                    @endphp
                                    @for ($m = 1; $m <= 12; $m++)
                                        <button type="button" @click="openBulan = false; navigateTo({{ $m }}, currentTahun);" :class="currentBulan === {{ $m }} ? 'bg-amber-50 text-amber-700' : ''" class="w-full text-left px-4 py-3 text-sm font-bold text-gray-600 hover:bg-amber-50 hover:text-amber-700 transition">
                                            {{ $bulanNames[$m - 1] }}
                                        </button>
                                    @endfor
                                </div>
                            </div>

                            {{-- Tombol Next (between bulan and tahun) --}}
                            <button type="button" @click="
                                let newBulan = currentBulan + 1;
                                let newTahun = currentTahun;
                                if (newBulan > 12) {
                                    newBulan = 1;
                                    newTahun++;
                                }
                                navigateTo(newBulan, newTahun);
                            " class="p-3 bg-gray-100 hover:bg-amber-700 text-gray-600 hover:text-white rounded-xl transition-all flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>

                            {{-- Tahun Dropdown --}}
                            <div class="flex-1 sm:flex-none relative">
                                <button type="button" @click="openTahun = !openTahun" class="w-full sm:w-auto bg-gray-50 border-none rounded-xl px-4 py-3 text-sm font-bold text-gray-600 outline-none focus:ring-2 focus:ring-amber-500 transition flex items-center justify-between hover:bg-gray-100 min-w-[100px]">
                                    <span x-text="currentTahun"></span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transition-transform" :class="openTahun ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div
                                    x-show="openTahun"
                                    x-cloak
                                    @click.away="openTahun = false"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 -translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-2"
                                    class="absolute top-full left-0 mt-2 bg-white border border-gray-100 rounded-xl shadow-xl z-[9999] overflow-y-auto max-h-80 min-w-[100px]"
                                >
                                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                                        <button type="button" @click="openTahun = false; navigateTo(currentBulan, {{ $y }});" :class="currentTahun === {{ $y }} ? 'bg-amber-50 text-amber-700' : ''" class="w-full text-left px-4 py-3 text-sm font-bold text-gray-600 hover:bg-amber-50 hover:text-amber-700 transition">
                                            {{ $y }}
                                        </button>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bagian: Tabel Data --}}
                <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden" data-aos="fade-up">
                    <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                        <h2 class="font-bold text-gray-900">Daftar Keuangan</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                        <a href="{{ route('admin.keuangan.index', array_merge(request()->query(), ['sort_by' => 'tanggal', 'order' => request('sort_by') == 'tanggal' && request('order') == 'asc' ? 'desc' : 'asc', 'bulan' => $bulan, 'tahun' => $tahun])) }}" class="flex items-center gap-1 hover:text-amber-700 transition-colors">
                                            Tanggal
                                            @if(request('sort_by') == 'tanggal')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 {{ request('order') == 'asc' ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Keterangan</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                        <a href="{{ route('admin.keuangan.index', array_merge(request()->query(), ['sort_by' => 'tipe_laporan', 'order' => request('sort_by') == 'tipe_laporan' && request('order') == 'asc' ? 'desc' : 'asc', 'bulan' => $bulan, 'tahun' => $tahun])) }}" class="flex items-center gap-1 hover:text-amber-700 transition-colors">
                                            Tipe
                                            @if(request('sort_by') == 'tipe_laporan')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 {{ request('order') == 'asc' ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                        <a href="{{ route('admin.keuangan.index', array_merge(request()->query(), ['sort_by' => 'nominal', 'order' => request('sort_by') == 'nominal' && request('order') == 'asc' ? 'desc' : 'asc', 'bulan' => $bulan, 'tahun' => $tahun])) }}" class="flex items-center gap-1 hover:text-amber-700 transition-colors">
                                            Nominal
                                            @if(request('sort_by') == 'nominal')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 {{ request('order') == 'asc' ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50" id="keuangan-table-body">
                                @php
                                    // Merge keuangan dan transaksi data
                                    $allData = collect();
                                    
                                    // Add keuangan data
                                    foreach($keuangans as $k) {
                                        $allData->push([
                                            'type' => 'keuangan',
                                            'data' => $k,
                                        ]);
                                    }
                                    
                                    // Add transaksi data
                                    foreach($transaksis as $t) {
                                        $allData->push([
                                            'type' => 'transaksi',
                                            'data' => $t,
                                        ]);
                                    }
                                    
                                    // Sort by tanggal/tanggal_transaksi
                                    $allData = $allData->sortByDesc(function($item) {
                                        return $item['type'] === 'keuangan' 
                                            ? $item['data']->tanggal 
                                            : $item['data']->tanggal_transaksi;
                                    })->values();
                                @endphp
                                
                                @forelse($allData as $item)
                                @php
                                    $isFromTransaksi = $item['type'] === 'transaksi';
                                    $tanggal = $isFromTransaksi ? $item['data']->tanggal_transaksi : $item['data']->tanggal;
                                    $keterangan = $isFromTransaksi ? 'transaksi ' . $item['data']->order_id : $item['data']->keterangan;
                                    $nominal = $isFromTransaksi ? $item['data']->total_harga : $item['data']->nominal;
                                    $tipe = $isFromTransaksi ? 'pendapatan' : $item['data']->tipe_laporan;
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition-colors {{ $isFromTransaksi ? 'bg-blue-50/30' : '' }}">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ $tanggal->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <div class="flex items-center gap-2">
                                            {{ $keterangan }}
                                            @if($isFromTransaksi)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-600 text-[10px] font-bold rounded-md uppercase">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                                Transaksi
                                            </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-3 py-1 rounded-full font-bold text-xs {{ $tipe === 'pendapatan' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-700' }}">
                                            {{ $tipe === 'pendapatan' ? 'Pendapatan' : 'Pengeluaran' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                        Rp {{ number_format($nominal, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="flex items-center justify-center gap-2">
                                            @if($isFromTransaksi)
                                                <div class="inline-flex items-center justify-center h-10 w-10 rounded-xl bg-gray-100 text-gray-400 cursor-not-allowed" title="Data dari transaksi tidak dapat diubah">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </div>
                                                <div class="inline-flex items-center justify-center h-10 w-10 rounded-xl bg-gray-100 text-gray-400 cursor-not-allowed" title="Data dari transaksi tidak dapat dihapus">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </div>
                                            @else
                                                <button @click="
                                                    showEditModal = true;
                                                    editUrl = '{{ route('admin.keuangan.update', $item['data']->id) }}';
                                                    editData = {
                                                        keterangan: '{{ addslashes($item['data']->keterangan) }}',
                                                        nominal: '{{ $item['data']->nominal }}',
                                                        tipe_laporan: '{{ $item['data']->tipe_laporan }}',
                                                        tanggal: '{{ $item['data']->tanggal->format('Y-m-d') }}',
                                                    };
                                                " type="button" class="inline-flex items-center justify-center h-10 w-10 rounded-xl bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white transition-all" title="Edit">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                <button @click="
                                                    showDeleteModal = true;
                                                    deleteUrl = '{{ route('admin.keuangan.destroy', $item['data']->id) }}';
                                                " type="button" class="inline-flex items-center justify-center h-10 w-10 rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all" title="Hapus">
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
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="text-gray-500 font-medium">Tidak ada data keuangan untuk bulan ini</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.keuangan.modal-create')
    @include('admin.keuangan.modal-edit')
    @include('admin.keuangan.modal-delete')

</div>
@endsection
