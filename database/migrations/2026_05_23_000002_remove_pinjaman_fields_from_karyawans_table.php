<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn(['pinjaman', 'angsuran', 'subsidi_pinjaman']);
        });
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->decimal('pinjaman', 15, 2)->default(0)->after('tunjangan');
            $table->unsignedInteger('angsuran')->default(0)->after('pinjaman');
            $table->decimal('subsidi_pinjaman', 15, 2)->default(0)->after('angsuran');
        });
    }
};
