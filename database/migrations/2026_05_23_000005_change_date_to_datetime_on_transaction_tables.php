<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'purchases', 'sales', 'capitals', 'expenses',
        'loris', 'lori_expenses', 'petty_cash_transactions',
        'bank_transactions', 'hutangs', 'piutangs',
        'uang_koordinasis', 'stock_transfers', 'stock_usages',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            \DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `date` DATETIME NOT NULL");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            \DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `date` DATE NOT NULL");
        }
    }
};
