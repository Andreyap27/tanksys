<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loris', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date');
            $table->foreignUuid('customer_id')->constrained('customers');
            $table->string('from')->comment('Lokasi asal');
            $table->string('to')->comment('Lokasi tujuan');
            $table->decimal('price', 15, 2)->comment('Harga / ongkos angkut');
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loris');
    }
};
