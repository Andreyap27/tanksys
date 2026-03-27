<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date');
            $table->string('invoice_number')->unique()->comment('No Invoice');
            $table->foreignUuid('customer_id')->constrained('customers');
            $table->string('description')->nullable();
            $table->decimal('quantity', 15, 2)->comment('Jumlah liter');
            $table->decimal('price', 15, 2)->comment('Harga per liter');
            $table->decimal('amount', 15, 2)->comment('Total = quantity * price');
            $table->text('noted')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
