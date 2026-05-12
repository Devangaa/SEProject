<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('akun');
            $table->string('order_id')->unique();
            $table->foreignId('kecamatan_id')->constrained('kecamatans');
            $table->text('alamat_pengiriman');
            $table->string('nama_penerima');
            $table->string('no_hp');
            $table->timestamp('tanggal_transaksi');
            $table->string('metode_pembayaran');
            $table->string('ekspedisi')->nullable();
            $table->string('status');
            $table->integer('poin')->default(0);
            $table->text('kode_pembayaran')->nullable();
            $table->timestamp('batas_pembayaran')->nullable();
            $table->string('nomor_resi')->nullable();
            $table->decimal('ongkir', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
