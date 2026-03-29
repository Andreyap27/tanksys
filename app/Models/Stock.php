<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasUuids;

    protected $fillable = [
        'date',
        'type',
        'reference_id',
        'reference_type',
        'party',
        'qty_in',
        'qty_out',
        'balance',
    ];

    protected function casts(): array
    {
        return [
            'date'    => 'date',
            'qty_in'  => 'decimal:2',
            'qty_out' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public static function currentBalance(): float
    {
        return (float) (static::sum('qty_in') - static::sum('qty_out'));
    }
}
