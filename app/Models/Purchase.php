<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasUuids;

    protected $fillable = [
        'date',
        'vendor',
        'description',
        'quantity',
        'price',
        'amount',
        'noted',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date'     => 'date',
            'quantity' => 'decimal:2',
            'price'    => 'decimal:2',
            'amount'   => 'decimal:2',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stock()
    {
        return $this->morphOne(Stock::class, 'reference');
    }
}
