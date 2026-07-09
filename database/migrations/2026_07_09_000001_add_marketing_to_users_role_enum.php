<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('SPV', 'Admin', 'Super Admin', 'Finance', 'Marketing') NOT NULL DEFAULT 'Admin'");
    }

    public function down(): void
    {
        \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('SPV', 'Admin', 'Super Admin', 'Finance') NOT NULL DEFAULT 'Admin'");
    }
};
