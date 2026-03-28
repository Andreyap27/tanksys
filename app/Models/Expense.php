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
        'BBM ME',
        'BBM AE',
        'Umum',
        'Fee',
        'Lori',
    ];

    const EXPENSE_CATEGORIES = [
        'Gaji',
        'Spare Part',
        'Jasa',
        'BBM ME',
        'BBM AE',
        'Umum',
        'Fee',
    ];

    protected $fillable = [
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
