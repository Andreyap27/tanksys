<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->enum('warna', ['merah', 'biru', 'kuning'])->nullable()->after('description');
            $table->decimal('extra', 15, 2)->default(0)->after('quantity')->comment('Qty bonus (masuk stok keluar, tidak masuk amount)');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['warna', 'extra']);
        });
    }
};
