<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date');
            $table->enum('type', ['purchase', 'sale'])->comment('Sumber transaksi');
            $table->uuid('reference_id')->comment('UUID dari purchases atau sales');
            $table->string('reference_type')->comment('App\Models\Purchase atau App\Models\Sale');
            $table->string('party')->comment('Vendor (IN) atau Customer (OUT)');
            $table->decimal('qty_in', 15, 2)->default(0)->comment('Jumlah masuk (purchase)');
            $table->decimal('qty_out', 15, 2)->default(0)->comment('Jumlah keluar (sale)');
            $table->decimal('balance', 15, 2)->comment('Saldo stok setelah transaksi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
