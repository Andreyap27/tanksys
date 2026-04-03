<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lori extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'mobil_id',
        'date',
        'customer_id',
        'from',
        'to',
        'price',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date'  => 'date',
            'price' => 'decimal:2',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
