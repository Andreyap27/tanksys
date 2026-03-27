<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date');
            $table->string('description');
            $table->decimal('nominal', 15, 2);
            $table->enum('category', [
                'Gaji',
                'Spare Part',
                'Jasa',
                'BBM ME',
                'BBM AE',
                'Umum',
                'Fee',
                'Lori',
            ])->comment('Kategori pengeluaran');
            $table->text('noted')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
