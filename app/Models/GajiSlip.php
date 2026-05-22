<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GajiSlip extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'karyawan_id',
        'period',
        'gaji_pokok',
        'tunjangan',
        'extra',
        'bonus',
        'potongan_pinjaman',
        'potongan_lain',
        'total',
        'noted',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'period'            => 'date',
            'approved_at'       => 'datetime',
            'gaji_pokok'        => 'decimal:2',
            'tunjangan'         => 'decimal:2',
            'extra'             => 'decimal:2',
            'bonus'             => 'decimal:2',
            'potongan_pinjaman' => 'decimal:2',
            'potongan_lain'     => 'decimal:2',
            'total'             => 'decimal:2',
        ];
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
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
