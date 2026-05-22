<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Karyawan extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'nama_karyawan',
        'no_ktp',
        'no_hp',
        'jabatan',
        'gaji_pokok',
        'tunjangan',
        'noted',
        'created_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'gaji_pokok' => 'decimal:2',
            'tunjangan'  => 'decimal:2',
        ];
    }

    public function slips()
    {
        return $this->hasMany(GajiSlip::class);
    }

    public function pinjamans()
    {
        return $this->hasMany(KaryawanPinjaman::class);
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
