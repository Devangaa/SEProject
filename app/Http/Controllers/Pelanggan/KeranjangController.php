<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Keranjang;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KeranjangController extends Controller
{
    /**
     * Tampilkan Halaman Keranjang
     */
    public function index()
    {
        $cartItems = Keranjang::where('user_id', Auth::id())
            ->with('product')
            ->get();

        $cartJson = $cartItems->map(function ($item) {
            return [
                'id' => $item->id,
                'price' => (float) $item->product->harga,
                'qty' => (int) $item->jumlah,
                'stok' => (int) $item->product->jumlah_stok,
            ];
        });

        return view('pelanggan.cart.index', compact('cartItems', 'cartJson'));
    }

    /**
     * Tambah Produk ke Keranjang
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'jumlah' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Cek apakah produk sudah ada di keranjang
        $cart = Keranjang::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($cart) {
            // Update jumlah jika sudah ada
            $newJumlah = $cart->jumlah + $request->jumlah;

            // Validasi stok
            if ($newJumlah > $product->jumlah_stok) {
                return back()->with('error', 'Jumlah melebihi stok yang tersedia.');
            }

            $cart->update(['jumlah' => $newJumlah]);
        } else {
            // Buat baru jika belum ada
            if ($request->jumlah > $product->jumlah_stok) {
                return back()->with('error', 'Jumlah melebihi stok yang tersedia.');
            }

            Keranjang::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'jumlah' => $request->jumlah,
            ]);
        }

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    /**
     * Update Jumlah Item (Real-time via AJAX)
     */
    public function update(Request $request, Keranjang $cart)
    {
        // Security check
        if ($cart->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'jumlah' => 'required|integer|min:1',
        ]);

        $product = $cart->product;
        if ($request->jumlah > $product->jumlah_stok) {
            return response()->json([
                'success' => false,
                'message' => 'Stok tidak mencukupi.',
                'max_stok' => $product->jumlah_stok,
            ], 400);
        }

        $cart->update(['jumlah' => $request->jumlah]);

        return response()->json([
            'success' => true,
            'message' => 'Jumlah diperbarui',
            'subtotal' => number_format($cart->jumlah * $product->harga, 0, ',', '.'),
        ]);
    }

    /**
     * Hapus Item dari Keranjang
     */
    public function destroy(Keranjang $cart)
    {
        if ($cart->user_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized');
        }

        $cart->delete();

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }
}
