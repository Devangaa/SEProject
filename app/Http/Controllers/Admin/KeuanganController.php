<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Keuangan;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Modul: Admin - Manajemen Keuangan
 * Fitur: CRUD keuangan, filter per bulan, statistik kas/pemasukan/pengeluaran, dan soft delete.
 */
class KeuanganController extends Controller
{
    /**
     * Bagian: Listing keuangan dengan statistik per bulan.
     */
    public function index(Request $request)
    {
        // Ambil bulan dan tahun dari request, default ke bulan dan tahun sekarang
        $bulan = $request->query('bulan', now()->month);
        $tahun = $request->query('tahun', now()->year);

        // Query keuangan aktif (bukan dihapus) berdasarkan bulan dan tahun
        $query = Keuangan::where('is_delete', false)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan);

        // Sorting logic
        $sortBy = $request->get('sort_by', 'tanggal');
        $order = $request->get('order', 'desc');

        $allowedSorts = ['tanggal', 'tipe_laporan', 'nominal'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $order);
        } else {
            $query->orderBy('tanggal', 'desc');
        }

        // Ambil semua data keuangan (tidak ada pagination)
        $keuangans = $query->get();

        // Ambil transaksi selesai untuk ditampilkan sebagai pendapatan (tidak disimpan ke DB)
        $transaksis = Transaksi::where('status', 'Selesai')
            ->whereYear('tanggal_transaksi', $tahun)
            ->whereMonth('tanggal_transaksi', $bulan)
            ->get();

        // Hitung statistik dari keuangan
        $semuaKeuangan = Keuangan::where('is_delete', false)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->get();

        $pendapatan = $semuaKeuangan->where('tipe_laporan', 'pendapatan')->sum('nominal');
        $pengeluaran = $semuaKeuangan->where('tipe_laporan', 'pengeluaran')->sum('nominal');

        // Tambahkan transaksi selesai ke pendapatan
        $transaksiTotal = $transaksis->sum(function($t) {
            return $t->total_harga;
        });
        $pendapatan += $transaksiTotal;

        $totalKas = $pendapatan - $pengeluaran;

        // Ambil daftar bulan dan tahun yang ada data
        $availableMonths = Keuangan::selectRaw('YEAR(tanggal) as tahun, MONTH(tanggal) as bulan')
            ->where('is_delete', false)
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        return view('admin.keuangan.index', compact(
            'keuangans',
            'transaksis',
            'pendapatan',
            'pengeluaran',
            'totalKas',
            'bulan',
            'tahun',
            'availableMonths'
        ));
    }

    /**
     * Bagian: Simpan keuangan baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'keterangan' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'tipe_laporan' => 'required|in:pendapatan,pengeluaran',
            'tanggal' => 'required|date',
        ], [
            'keterangan.required' => 'Keterangan wajib diisi.',
            'nominal.required' => 'Nominal harus diisi.',
            'nominal.numeric' => 'Nominal harus berupa angka.',
            'nominal.min' => 'Nominal tidak boleh kurang dari 0.',
            'tipe_laporan.required' => 'Tipe laporan harus dipilih.',
            'tipe_laporan.in' => 'Tipe laporan tidak valid.',
            'tanggal.required' => 'Tanggal harus diisi.',
            'tanggal.date' => 'Tanggal tidak valid.',
        ]);

        Keuangan::create($request->all());

        $bulan = Carbon::parse($request->tanggal)->month;
        $tahun = Carbon::parse($request->tanggal)->year;

        return redirect()->route('admin.keuangan.index', ['bulan' => $bulan, 'tahun' => $tahun])
            ->with('success', 'Data keuangan berhasil ditambahkan!');
    }

    /**
     * Bagian: Update keuangan.
     */
    public function update(Request $request, $id)
    {
        $keuangan = Keuangan::findOrFail($id);

        $request->validate([
            'keterangan' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'tipe_laporan' => 'required|in:pendapatan,pengeluaran',
            'tanggal' => 'required|date',
        ]);

        $bulanLama = $keuangan->tanggal->month;
        $tahunLama = $keuangan->tanggal->year;

        $keuangan->update($request->all());

        $bulanBaru = $keuangan->tanggal->month;
        $tahunBaru = $keuangan->tanggal->year;

        // Jika bulan/tahun berubah, redirect ke bulan baru
        if ($bulanLama !== $bulanBaru || $tahunLama !== $tahunBaru) {
            return redirect()->route('admin.keuangan.index', ['bulan' => $bulanBaru, 'tahun' => $tahunBaru])
                ->with('success', 'Data keuangan berhasil diperbarui!');
        }

        return redirect()->back()->with('success', 'Data keuangan berhasil diperbarui!');
    }

    /**
     * Bagian: Hapus keuangan (soft delete).
     */
    public function destroy($id)
    {
        $keuangan = Keuangan::findOrFail($id);
        $keuangan->update(['is_delete' => true]);

        return redirect()->back()->with('success', 'Data keuangan berhasil dihapus!');
    }
}
