<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lori_expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date');
            $table->string('description');
            $table->enum('category', ['BBM', 'Gaji', 'Maintenance', 'Umum']);
            $table->decimal('nominal', 15, 2);
            $table->text('noted')->nullable();
            $table->uuid('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lori_expenses');
    }
};
