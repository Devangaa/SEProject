{{-- Modal: Tambah Laporan Keuangan --}}
<template x-teleport="body">
    <div x-show="showCreateModal" 
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        
        <div @click.stop
            @click.away="showCreateModal = false"
            x-show="showCreateModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md relative">
            
            {{-- Bagian: Header Modal --}}
            <div class="px-8 py-6 border-b border-gray-50 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Tambah Laporan Keuangan</h1>
                <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Bagian: Form --}}
            <form action="{{ route('admin.keuangan.store') }}" method="POST" class="p-8 space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-100 rounded-2xl p-4 flex items-start gap-3 text-red-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-bold text-sm">Gagal menambah laporan:</p>
                            <ul class="list-disc list-inside text-xs mt-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div>
                    <label for="keterangan" class="text-sm font-bold text-gray-700 ml-1 mb-2 block">Keterangan</label>
                    <input type="text" id="keterangan" name="keterangan" value="{{ old('keterangan') }}" placeholder="Masukkan keterangan" class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-green-500 outline-none text-sm {{ $errors->has('keterangan') ? 'ring-2 ring-red-500' : '' }}" required>
                    @error('keterangan')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div x-data="{ openTipe: false, selectedType: 'pengeluaran' }" class="relative">
                    <label class="text-sm font-bold text-gray-700 ml-1 mb-2 block">Tipe Laporan</label>
                    <button type="button" @click="openTipe = !openTipe" class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-gray-600 outline-none focus:ring-2 focus:ring-green-500 transition flex items-center justify-between hover:bg-gray-100">
                        <span x-text="selectedType === 'pendapatan' ? 'Pendapatan' : 'Pengeluaran'"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transition-transform" :class="openTipe ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openTipe" x-cloak @click.away="openTipe = false" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-100 rounded-2xl shadow-xl z-[9999] overflow-hidden">
                        <input type="hidden" id="tipe_laporan" name="tipe_laporan" :value="selectedType">
                        <button type="button" @click="openTipe = false; selectedType = 'pengeluaran'; document.getElementById('tipe_laporan').value = 'pengeluaran';" :class="selectedType === 'pengeluaran' ? 'bg-red-50 text-red-600' : 'text-gray-600'" class="w-full text-left px-5 py-3 text-sm font-bold hover:bg-red-50 hover:text-red-600 transition">
                            Pengeluaran
                        </button>
                        <button type="button" @click="openTipe = false; selectedType = 'pendapatan'; document.getElementById('tipe_laporan').value = 'pendapatan';" :class="selectedType === 'pendapatan' ? 'bg-green-50 text-green-600' : 'text-gray-600'" class="w-full text-left px-5 py-3 text-sm font-bold hover:bg-green-50 hover:text-green-600 transition">
                            Pendapatan
                        </button>
                    </div>
                    @error('tipe_laporan')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="nominal" class="text-sm font-bold text-gray-700 ml-1 mb-2 block">Nominal</label>
                    <input type="number" id="nominal" name="nominal" value="{{ old('nominal') }}" placeholder="Masukkan nominal" class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-green-500 outline-none text-sm [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none {{ $errors->has('nominal') ? 'ring-2 ring-red-500' : '' }}" min="0" step="0.01" required>
                    @error('nominal')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div x-data="{ 
                    selectedDate: '{{ old('tanggal') ?? now()->format('Y-m-d') }}',
                    showCalendar: false,
                    currentMonth: {{ now()->month }},
                    currentYear: {{ now()->year }},
                    get calendarDays() {
                        const days = [];
                        const firstDay = new Date(this.currentYear, this.currentMonth - 1, 1).getDay();
                        const daysInMonth = new Date(this.currentYear, this.currentMonth, 0).getDate();
                        for (let i = 0; i < firstDay; i++) days.push(null);
                        for (let i = 1; i <= daysInMonth; i++) days.push(i);
                        return days;
                    },
                    monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                    dayNames: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    selectDate(day) {
                        if (day) {
                            const date = new Date(this.currentYear, this.currentMonth - 1, day);
                            this.selectedDate = date.toISOString().split('T')[0];
                            document.getElementById('tanggal').value = this.selectedDate;
                            this.showCalendar = false;
                        }
                    },
                    prevMonth() {
                        this.currentMonth--;
                        if (this.currentMonth < 1) {
                            this.currentMonth = 12;
                            this.currentYear--;
                        }
                    },
                    nextMonth() {
                        this.currentMonth++;
                        if (this.currentMonth > 12) {
                            this.currentMonth = 1;
                            this.currentYear++;
                        }
                    }
                }">
                    <label for="tanggal" class="text-sm font-bold text-gray-700 ml-1 mb-2 block">Tanggal</label>
                    <div class="relative">
                        <input type="hidden" id="tanggal" name="tanggal" :value="selectedDate">
                        <button type="button" @click="showCalendar = !showCalendar" class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-green-500 outline-none text-sm font-bold text-gray-600 text-left flex items-center justify-between hover:bg-gray-100 {{ $errors->has('tanggal') ? 'ring-2 ring-red-500' : '' }}">
                            <span x-text="new Date(selectedDate + 'T00:00:00').toLocaleDateString('id-ID', {year: 'numeric', month: 'long', day: 'numeric'})"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </button>
                        
                        {{-- Calendar Popup --}}
                        <div x-show="showCalendar" @click.away="showCalendar = false" x-transition class="absolute top-full left-0 mt-2 bg-white border border-gray-100 rounded-2xl shadow-xl z-50 p-4 min-w-[280px]">
                            {{-- Header --}}
                            <div class="flex items-center justify-between mb-4">
                                <button type="button" @click="prevMonth()" class="p-2 hover:bg-gray-100 rounded-lg transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <div class="text-center flex-1">
                                    <p class="text-sm font-bold text-gray-900" x-text="`${monthNames[currentMonth - 1]} ${currentYear}`"></p>
                                </div>
                                <button type="button" @click="nextMonth()" class="p-2 hover:bg-gray-100 rounded-lg transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Day Headers --}}
                            <div class="grid grid-cols-7 gap-1 mb-2">
                                <template x-for="dayName in dayNames" :key="dayName">
                                    <div class="text-center text-xs font-bold text-gray-400 py-2">
                                        <span x-text="dayName"></span>
                                    </div>
                                </template>
                            </div>

                            {{-- Calendar Days --}}
                            <div class="grid grid-cols-7 gap-1">
                                <template x-for="(day, index) in calendarDays" :key="index">
                                    <button 
                                        type="button"
                                        :hidden="day === null"
                                        :disabled="day === null"
                                        @click="day && selectDate(day)"
                                        :class="day && selectedDate === `${String(currentYear).padStart(4, '0')}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}` 
                                            ? 'bg-green-600 text-white font-bold' 
                                            : 'bg-gray-50 text-gray-600 hover:bg-green-50 hover:text-green-600'"
                                        class="p-2 rounded-lg text-sm font-semibold transition-all"
                                        x-text="day"
                                    >
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                    @error('tanggal')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Bagian: Tombol Aksi --}}
                <div class="flex flex-col gap-3 pt-4">
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-2xl transition-all shadow-lg shadow-green-100">
                        Simpan Laporan
                    </button>
                    <button type="button" @click="showCreateModal = false" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-4 rounded-2xl transition-all">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
