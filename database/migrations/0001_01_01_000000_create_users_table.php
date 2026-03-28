<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('employee_id')->unique()->comment('No ID Karyawan');
            $table->string('name')->comment('Nama Karyawan');
            $table->enum('role', ['Super Admin', 'SPV', 'Admin'])->default('Admin');
            $table->string('username')->unique();
            $table->string('password');
            $table->boolean('reset_password')->default(false)->comment('Flag wajib ganti password saat login');
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('username')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
