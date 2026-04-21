<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date');
            $table->enum('type', ['in', 'out'])->comment('in=kredit, out=debit');
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->enum('note', ['Gaji', 'Umum', 'Koordinasi', 'Buy', 'Sell']);
            $table->string('job');
            $table->foreignUuid('created_by')->constrained('users');
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
