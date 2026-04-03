<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use HasUuids, SoftDeletes;

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
        'deleted_by',
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

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public static function currentBalance(?string $kapalId = null): float
    {
        $query = static::query();
        if ($kapalId) $query->where('kapal_id', $kapalId);
        return (float) ($query->sum('qty_in') - $query->sum('qty_out'));
    }

    protected static function booted()
    {
        static::deleting(function ($model) {
            if ($model->isForceDeleting()) return;
            $model->deleted_by = auth()->id();
        });
    }
}
