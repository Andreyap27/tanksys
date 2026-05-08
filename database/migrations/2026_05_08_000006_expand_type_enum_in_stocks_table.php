<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE stocks MODIFY COLUMN type ENUM('purchase','sale','transfer_in','transfer_out','usage') NOT NULL COMMENT 'Sumber transaksi'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stocks MODIFY COLUMN type ENUM('purchase','sale') NOT NULL COMMENT 'Sumber transaksi'");
    }
};
