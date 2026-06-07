{{-- Modal: Edit Laporan Keuangan --}}
<template x-teleport="body">
    <div x-show="showEditModal" 
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        
        <div @click.stop
            @click.away="showEditModal = false"
            x-show="showEditModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md relative">
            
            {{-- Bagian: Header Modal --}}
            <div class="px-8 py-6 border-b border-gray-50 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Edit Laporan Keuangan</h1>
                <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Bagian: Form --}}
            <form :action="editUrl" method="POST" class="p-8 space-y-6">
                @csrf
                @method('PATCH')

                <div>
                    <label for="edit_keterangan" class="text-sm font-bold text-gray-700 ml-1 mb-2 block">Keterangan</label>
                    <input type="text" id="edit_keterangan" x-model="editData.keterangan" name="keterangan" placeholder="Masukkan keterangan" class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-amber-500 outline-none text-sm" required>
                </div>

                <div x-data="{ openTipeEdit: false }" class="relative">
                    <label class="text-sm font-bold text-gray-700 ml-1 mb-2 block">Tipe Laporan</label>
                    <button type="button" @click="openTipeEdit = !openTipeEdit" class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-gray-600 outline-none focus:ring-2 focus:ring-amber-500 transition flex items-center justify-between hover:bg-gray-100" :style="{ backgroundColor: editData.tipe_laporan === 'pendapatan' ? '#dcfce7' : '#fee2e2', color: editData.tipe_laporan === 'pendapatan' ? '#15803d' : '#b91c1c' }">
                        <span x-text="editData.tipe_laporan === 'pendapatan' ? 'Pendapatan' : 'Pengeluaran'"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="openTipeEdit ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="openTipeEdit" x-cloak @click.away="openTipeEdit = false" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-100 rounded-2xl shadow-xl z-[9999] overflow-hidden">
                        <input type="hidden" id="edit_tipe_laporan" x-model="editData.tipe_laporan" name="tipe_laporan">
                        <button type="button" @click="openTipeEdit = false; editData.tipe_laporan = 'pengeluaran';" :class="editData.tipe_laporan === 'pengeluaran' ? 'bg-red-50 text-red-600' : 'text-gray-600'" class="w-full text-left px-5 py-3 text-sm font-bold hover:bg-red-50 hover:text-red-600 transition">
                            Pengeluaran
                        </button>
                        <button type="button" @click="openTipeEdit = false; editData.tipe_laporan = 'pendapatan';" :class="editData.tipe_laporan === 'pendapatan' ? 'bg-amber-50 text-amber-700' : 'text-gray-600'" class="w-full text-left px-5 py-3 text-sm font-bold hover:bg-amber-50 hover:text-amber-700 transition">
                            Pendapatan
                        </button>
                    </div>
                </div>

                <div>
                    <label for="edit_nominal" class="text-sm font-bold text-gray-700 ml-1 mb-2 block">Nominal</label>
                    <input type="number" id="edit_nominal" x-model="editData.nominal" name="nominal" placeholder="Masukkan nominal" class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-amber-500 outline-none text-sm [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" min="0" step="0.01" required>
                </div>

                <div>
                    <label for="edit_tanggal" class="text-sm font-bold text-gray-700 ml-1 mb-2 block">Tanggal</label>
                    <div class="relative">
                        <input type="hidden" id="edit_tanggal" x-model="editData.tanggal" name="tanggal">
                        <button type="button" @click="showCalendarEdit = !showCalendarEdit" class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-amber-500 outline-none text-sm font-bold text-gray-600 text-left flex items-center justify-between hover:bg-gray-100">
                            <span x-text="editData.tanggal ? new Date(editData.tanggal + 'T00:00:00').toLocaleDateString('id-ID', {year: 'numeric', month: 'long', day: 'numeric'}) : 'Pilih Tanggal'"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </button>
                        
                        {{-- Calendar Popup Edit --}}
                        <div x-show="showCalendarEdit" @click.away="showCalendarEdit = false" x-transition class="absolute top-full left-0 mt-2 bg-white border border-gray-100 rounded-2xl shadow-xl z-50 p-4 min-w-[280px]">
                            {{-- Header --}}
                            <div class="flex items-center justify-between mb-4">
                                <button type="button" @click="prevMonthEdit()" class="p-2 hover:bg-gray-100 rounded-lg transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <div class="text-center flex-1">
                                    <p class="text-sm font-bold text-gray-900" x-text="`${monthNamesEdit[currentMonthEdit - 1]} ${currentYearEdit}`"></p>
                                </div>
                                <button type="button" @click="nextMonthEdit()" class="p-2 hover:bg-gray-100 rounded-lg transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Day Headers --}}
                            <div class="grid grid-cols-7 gap-1 mb-2">
                                <template x-for="dayName in dayNamesEdit" :key="dayName">
                                    <div class="text-center text-xs font-bold text-gray-400 py-2">
                                        <span x-text="dayName"></span>
                                    </div>
                                </template>
                            </div>

                            {{-- Calendar Days --}}
                            <div class="grid grid-cols-7 gap-1">
                                <template x-for="(day, index) in calendarDaysEdit" :key="index">
                                    <button 
                                        type="button"
                                        :hidden="day === null"
                                        :disabled="day === null"
                                        @click="day && selectDateEdit(day)"
                                        :class="day && editData.tanggal === `${String(currentYearEdit).padStart(4, '0')}-${String(currentMonthEdit).padStart(2, '0')}-${String(day).padStart(2, '0')}` 
                                            ? 'bg-amber-700 text-white font-bold' 
                                            : 'bg-gray-50 text-gray-600 hover:bg-amber-50 hover:text-amber-700'"
                                        class="p-2 rounded-lg text-sm font-semibold transition-all"
                                        x-text="day"
                                    >
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bagian: Tombol Aksi --}}
                <div class="flex flex-col gap-3 pt-4">
                    <button type="submit" class="w-full bg-amber-700 hover:bg-amber-800 text-white font-bold py-4 rounded-2xl transition-all shadow-lg shadow-amber-100">
                        Update Laporan
                    </button>
                    <button type="button" @click="showEditModal = false" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-4 rounded-2xl transition-all">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
