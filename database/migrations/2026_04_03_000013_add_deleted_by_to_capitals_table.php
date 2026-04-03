<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capitals', function (Blueprint $table) {
            if (!Schema::hasColumn('capitals', 'deleted_by')) {
                $table->foreignUuid('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('capitals', function (Blueprint $table) {
            if (Schema::hasColumn('capitals', 'deleted_by')) {
                $table->dropForeign(['deleted_by']);
                $table->dropColumn('deleted_by');
            }
        });
    }
};
