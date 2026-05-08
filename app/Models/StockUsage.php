<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockUsage extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'date',
        'kapal_id',
        'warna',
        'quantity',
        'keperluan',
        'created_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'date'     => 'date',
            'quantity' => 'decimal:2',
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

    public function stock()
    {
        return $this->morphOne(Stock::class, 'reference');
    }

    protected static function booted()
    {
        static::deleting(function ($model) {
            if ($model->isForceDeleting()) return;
            $model->update(['deleted_by' => auth()->id()]);
        });
    }
}
