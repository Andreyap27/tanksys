<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'kapal_id',
        'date',
        'vendor',
        'description',
        'quantity',
        'price',
        'amount',
        'noted',
        'created_by',
        'status',
        'approved_by',
        'approved_at',
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

    public function kapal()
    {
        return $this->belongsTo(Kapal::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function stock()
    {
        return $this->morphOne(Stock::class, 'reference');
    }
}
