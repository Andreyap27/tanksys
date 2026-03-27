<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->comment('Nama Perusahaan / Perorangan');
            $table->text('address')->nullable()->comment('Alamat');
            $table->string('pic_name')->nullable()->comment('Nama PIC');
            $table->string('contact')->nullable()->comment('No Contact');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
