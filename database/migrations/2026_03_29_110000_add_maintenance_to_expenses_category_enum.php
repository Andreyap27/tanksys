<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        \DB::statement("ALTER TABLE expenses MODIFY COLUMN category ENUM('Gaji','Spare Part','Jasa','Maintenance','BBM ME','BBM AE','Umum','Fee','Lori') NOT NULL COMMENT 'Kategori pengeluaran'");
    }

    public function down(): void
    {
        \DB::statement("ALTER TABLE expenses MODIFY COLUMN category ENUM('Gaji','Spare Part','Jasa','BBM ME','BBM AE','Umum','Fee','Lori') NOT NULL COMMENT 'Kategori pengeluaran'");
    }
};
