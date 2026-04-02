<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('loris', 'mobil_id')) {
            Schema::table('loris', function (Blueprint $table) {
                $table->uuid('mobil_id')->nullable()->after('id');
                $table->foreign('mobil_id')->references('id')->on('mobils')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('loris', 'mobil_id')) {
            Schema::table('loris', function (Blueprint $table) {
                $table->dropForeign(['mobil_id']);
                $table->dropColumn('mobil_id');
            });
        }
    }
};
