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
        Schema::table('keuangans', function (Blueprint $table) {
            $table->unsignedBigInteger('transaksi_id')->nullable()->after('is_delete');
            $table->boolean('from_transaksi')->default(false)->after('transaksi_id');
            $table->foreign('transaksi_id')->references('id')->on('transaksis')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keuangans', function (Blueprint $table) {
            $table->dropForeign(['transaksi_id']);
            $table->dropColumn(['transaksi_id', 'from_transaksi']);
        });
    }
};
