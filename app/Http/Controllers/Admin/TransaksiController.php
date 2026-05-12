<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'menunggu-pembayaran');

        $query = Transaksi::with(['user', 'detailProduks.produk', 'detailLayanans.layanan'])
            ->orderBy('created_at', 'desc');

        if ($tab === 'menunggu-pembayaran') {
            $query->whereIn('status', ['Menunggu Pembayaran', 'Menunggu Konfirmasi']);
        } elseif ($tab === 'diproses') {
            $query->whereIn('status', ['Diproses', 'Dikirim']);
        } elseif ($tab === 'riwayat') {
            $query->whereIn('status', ['Selesai', 'Dibatalkan']);
        }

        $transaksis = $query->paginate(10)->withQueryString();

        return view('admin.transaksi.index', compact('transaksis', 'tab'));
    }

    public function show($order_id)
    {
        $transaksi = Transaksi::with(['user', 'kecamatan.city.province', 'detailProduks.produk', 'detailLayanans.layanan'])
            ->where('order_id', $order_id)
            ->firstOrFail();

        // Kecamatan list for regional logic
        $localKecamatans = ['Patrang', 'Sumbersari', 'Kaliwates'];
        $isLocal = in_array($transaksi->kecamatan->name, $localKecamatans);

        // Fetch Tracking Data with Cache (1 hour)
        $trackingData = null;
        if (in_array($transaksi->status, ['Dikirim', 'Selesai']) && $transaksi->nomor_resi && $transaksi->ekspedisi) {
            if ($transaksi->ekspedisi !== 'Kurir Lokal') {
                $cacheKey = 'tracking_'.$transaksi->nomor_resi;

                $trackingData = Cache::remember($cacheKey, 3600, function () use ($transaksi) {
                    if (strtoupper($transaksi->nomor_resi) === 'TESTING123') {
                        return $this->getMockTrackingData($transaksi->nomor_resi);
                    }

                    try {
                        $apiKey = config('services.binderbyte.key');
                        $response = Http::get('https://api.binderbyte.com/v1/track', [
                            'api_key' => $apiKey,
                            'courier' => strtolower($transaksi->ekspedisi),
                            'awb' => $transaksi->nomor_resi,
                        ]);

                        if ($response->successful()) {
                            $data = $response->json();
                            if (isset($data['status']) && $data['status'] === 200) {
                                return $data;
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('BinderByte API Show Error: '.$e->getMessage());
                    }

                    return null;
                });
            }
        }

        return view('admin.transaksi.show', compact('transaksi', 'isLocal', 'trackingData'));
    }

    public function updateStatus(Request $request, $order_id)
    {
        $transaksi = Transaksi::where('order_id', $order_id)->firstOrFail();
        $newStatus = $request->status;

        // Simple validation of sequential transitions
        $validTransitions = [
            'Menunggu Konfirmasi' => ['Diproses', 'Dibatalkan'],
            'Menunggu Pembayaran' => ['Dibatalkan'],
            'Diproses' => ['Dikirim'],
            'Dikirim' => ['Selesai'],
        ];

        if (! isset($validTransitions[$transaksi->status]) || ! in_array($newStatus, $validTransitions[$transaksi->status])) {
            return back()->with('error', 'Transisi status tidak valid.');
        }

        // Regional logic for Non-Cash manual "Dikirim"
        if ($newStatus === 'Dikirim' && $transaksi->metode_pembayaran !== 'cod') {
            $localKecamatans = ['Patrang', 'Sumbersari', 'Kaliwates'];
            if (! in_array($transaksi->kecamatan->name, $localKecamatans)) {
                return back()->with('error', 'Status "Dikirim" hanya bisa diubah manual untuk wilayah Patrang, Sumbersari, dan Kaliwates.');
            }
        }

        return DB::transaction(function () use ($transaksi, $newStatus) {
            $transaksi->status = $newStatus;
            $transaksi->save();

            // Berikan poin ke pelanggan jika status berubah menjadi Selesai
            if ($newStatus === 'Selesai') {
                $user = $transaksi->user;
                $user->increment('poin_reward', $transaksi->poin);
            }

            return back()->with('success', "Status pesanan berhasil diubah menjadi $newStatus.");
        });
    }

    public function updateResi(Request $request, $order_id)
    {
        $request->validate([
            'ekspedisi' => 'required|string',
            'nomor_resi' => 'required|string',
        ]);

        $transaksi = Transaksi::where('order_id', $order_id)->firstOrFail();

        // Support for Mock Testing
        if (strtoupper($request->nomor_resi) === 'TESTING123') {
            $transaksi->ekspedisi = $request->ekspedisi;
            $transaksi->nomor_resi = strtoupper($request->nomor_resi);
            $transaksi->status = 'Dikirim';
            $transaksi->save();

            return back()->with('success', 'Nomor resi MOCK berhasil dipasang. Pesanan telah dikirim.');
        }

        // Validate via Binder Byte API
        $apiKey = config('services.binderbyte.key');

        try {
            $response = Http::get('https://api.binderbyte.com/v1/track', [
                'api_key' => $apiKey,
                'courier' => strtolower($request->ekspedisi),
                'awb' => $request->nomor_resi,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['status']) && $data['status'] === 200) {
                // Success: Update resi and status
                $transaksi->ekspedisi = $request->ekspedisi;
                $transaksi->nomor_resi = $request->nomor_resi;
                $transaksi->status = 'Dikirim';
                $transaksi->save();

                return back()->with('success', 'Nomor resi valid. Pesanan telah dikirim.');
            } else {
                return back()->withErrors(['nomor_resi' => 'nomor resi tidak valid'])->withInput();
            }
        } catch (\Exception $e) {
            Log::error('BinderByte API Error: '.$e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat menghubungi layanan kurir.');
        }
    }

    private function getMockTrackingData($resi)
    {
        return [
            'status' => 200,
            'message' => 'Successfully tracked package (MOCK)',
            'data' => [
                'summary' => [
                    'awb' => $resi,
                    'courier' => 'JNE',
                    'service' => 'REG',
                    'status' => 'DELIVERED',
                    'date' => now()->format('Y-m-d H:i:s'),
                    'desc' => 'Paket telah diterima oleh [Mothyw]',
                ],
                'history' => [
                    [
                        'date' => now()->subHours(2)->format('Y-m-d H:i:s'),
                        'desc' => 'PESANAN TELAH DIANTAR KE ALAMAT TUJUAN',
                        'location' => 'JEMBER',
                    ],
                    [
                        'date' => now()->subHours(5)->format('Y-m-d H:i:s'),
                        'desc' => 'PAKET KELUAR DARI GUDANG (DC JEMBER)',
                        'location' => 'JEMBER',
                    ],
                    [
                        'date' => now()->subDays(1)->format('Y-m-d H:i:s'),
                        'desc' => 'PAKET SEDANG DALAM PERJALANAN (TRANSIT)',
                        'location' => 'SURABAYA',
                    ],
                    [
                        'date' => now()->subDays(1)->subHours(3)->format('Y-m-d H:i:s'),
                        'desc' => 'PESANAN DIPROSES DI PUSAT SORTIR',
                        'location' => 'JAKARTA',
                    ],
                ],
            ],
        ];
    }
}
