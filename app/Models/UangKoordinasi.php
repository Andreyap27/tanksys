<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UangKoordinasi extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'date',
        'nama',
        'jabatan',
        'kategori_biaya',
        'noted',
        'nominal',
        'extra',
        'total',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'date'    => 'datetime',
            'nominal' => 'decimal:2',
            'extra'   => 'decimal:2',
            'total'   => 'decimal:2',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    protected static function booted()
    {
        static::deleting(function ($model) {
            if ($model->isForceDeleting()) return;
            $model->update(['deleted_by' => auth()->id()]);
        });
    }
}
