<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date');
            $table->foreignUuid('kapal_id')->nullable()->constrained('kapals')->nullOnDelete();
            $table->enum('warna', ['merah', 'biru', 'kuning']);
            $table->decimal('quantity', 15, 2);
            $table->string('keperluan');
            $table->foreignUuid('created_by')->constrained('users');
            $table->foreignUuid('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_usages');
    }
};
