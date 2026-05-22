<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('purchases', 'short')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->decimal('short', 15, 2)->default(0)->after('extra');
            });
        }
        if (!Schema::hasColumn('sales', 'short')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('short', 15, 2)->default(0)->after('extra');
            });
        }
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('short');
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('short');
        });
    }
};
