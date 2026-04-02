<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->uuid('kapal_id')->nullable()->after('id');
            $table->foreign('kapal_id')->references('id')->on('kapals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropForeign(['kapal_id']);
            $table->dropColumn('kapal_id');
        });
    }
};
