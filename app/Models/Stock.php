<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasUuids;

    protected $fillable = [
        'kapal_id',
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

    public static function currentBalance(?string $kapalId = null): float
    {
        $query = static::query();
        if ($kapalId) $query->where('kapal_id', $kapalId);
        return (float) ($query->sum('qty_in') - $query->sum('qty_out'));
    }
}
