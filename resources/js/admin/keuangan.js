/**
 * ==============================================================================
 * FILE: admin/keuangan.js
 * HALAMAN: Kelola Keuangan
 * DESKRIPSI: Logika Alpine.js untuk manajemen modal, filter bulan, dan interaksi
 *            halaman keuangan termasuk CRUD operations dan calendar date picker.
 * ==============================================================================
 */

/**
 * Inisialisasi Alpine component untuk halaman keuangan
 * Properties: state management untuk modal, filter, calendar, dan data
 * Methods: navigasi, calendar handling, modal management
 */
export function initKeuanganComponent(phpData) {
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
        /**
         * Inisialisasi calendar untuk edit modal berdasarkan tanggal yang dipilih
         */
        initializeEditCalendar() {
            if (this.editData.tanggal) {
                const [year, month, day] = this.editData.tanggal.split('-').map(Number);
                this.currentYearEdit = year;
                this.currentMonthEdit = month;
            }
        },

        /**
         * Select tanggal dari calendar dan close calendar picker
         */
        selectDateEdit(day) {
            if (day) {
                const date = new Date(this.currentYearEdit, this.currentMonthEdit - 1, day);
                this.editData.tanggal = date.toISOString().split('T')[0];
                this.showCalendarEdit = false;
            }
        },

        /**
         * Navigate ke bulan sebelumnya di calendar
         */
        prevMonthEdit() {
            this.currentMonthEdit--;
            if (this.currentMonthEdit < 1) {
                this.currentMonthEdit = 12;
                this.currentYearEdit--;
            }
        },

        /**
         * Navigate ke bulan berikutnya di calendar
         */
        nextMonthEdit() {
            this.currentMonthEdit++;
            if (this.currentMonthEdit > 12) {
                this.currentMonthEdit = 1;
                this.currentYearEdit++;
            }
        },

        // ===== Methods: Navigation =====
        /**
         * Navigate ke bulan dan tahun tertentu dengan full page refresh
         */
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
}

/**
 * Wrapper function untuk Alpine component initialization
 * Memanggil initKeuanganComponent dengan data dari window.keuanganData
 */
window.keuanganComponent = function() {
    return initKeuanganComponent(window.keuanganData);
};

/**
 * Membuka modal edit dengan data keuangan yang dipilih
 */
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
