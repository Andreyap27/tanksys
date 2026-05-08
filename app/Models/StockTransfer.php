<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransfer extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'date',
        'from_kapal_id',
        'to_kapal_id',
        'warna',
        'quantity',
        'note',
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

    public function fromKapal()
    {
        return $this->belongsTo(Kapal::class, 'from_kapal_id');
    }

    public function toKapal()
    {
        return $this->belongsTo(Kapal::class, 'to_kapal_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stocks()
    {
        return $this->morphMany(Stock::class, 'reference');
    }

    protected static function booted()
    {
        static::deleting(function ($model) {
            if ($model->isForceDeleting()) return;
            $model->update(['deleted_by' => auth()->id()]);
        });
    }
}
