<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasUuids;

    const CATEGORIES = [
        'Gaji',
        'Spare Part',
        'Jasa',
        'Maintenance',
        'BBM ME',
        'BBM AE',
        'Umum',
        'Fee',
    ];

    const EXPENSE_CATEGORIES = [
        'Gaji',
        'Spare Part',
        'Jasa',
        'Maintenance',
        'BBM ME',
        'BBM AE',
        'Umum',
        'Fee',
    ];

    // Kategori yang dihitung sebagai biaya operasional Mobil Tangki
    const LORI_EXPENSE_CATEGORIES = [
        'Gaji',
        'Maintenance',
        'Umum',
    ];

    protected $fillable = [
        'kapal_id',
        'date',
        'description',
        'nominal',
        'category',
        'noted',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date'    => 'date',
            'nominal' => 'decimal:2',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
