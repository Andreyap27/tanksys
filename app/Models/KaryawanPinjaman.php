<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KaryawanPinjaman extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'karyawan_pinjamans';

    protected $fillable = [
        'karyawan_id',
        'pokok',
        'angsuran',
        'subsidi',
        'noted',
        'created_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'pokok'   => 'decimal:2',
            'subsidi' => 'decimal:2',
            'angsuran' => 'integer',
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
