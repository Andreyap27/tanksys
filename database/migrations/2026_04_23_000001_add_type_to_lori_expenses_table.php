<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lori_expenses', function (Blueprint $table) {
            $table->enum('type', ['in', 'out'])->default('out')->after('mobil_id')->comment('in=kredit, out=debit');
        });
    }

    public function down(): void
    {
        Schema::table('lori_expenses', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
