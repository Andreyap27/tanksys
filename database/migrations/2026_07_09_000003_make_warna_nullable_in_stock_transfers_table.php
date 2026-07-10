<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        \DB::statement("ALTER TABLE stock_transfers MODIFY COLUMN warna ENUM('merah', 'biru', 'kuning') NULL DEFAULT NULL");
    }

    public function down(): void
    {
        \DB::statement("ALTER TABLE stock_transfers MODIFY COLUMN warna ENUM('merah', 'biru', 'kuning') NOT NULL");
    }
};
