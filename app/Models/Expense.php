<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasUuids, SoftDeletes;

    const CATEGORIES = [
        'Gaji',
        'Spare Part',
        'Jasa',
        'Pinjaman',
        'BBM ME',
        'BBM AE',
        'Umum',
        'Fee',
    ];

    const EXPENSE_CATEGORIES = [
        'Gaji',
        'Spare Part',
        'Jasa',
        'Pinjaman',
        'BBM ME',
        'BBM AE',
        'Umum',
        'Fee',
    ];

    // Kategori yang dihitung sebagai biaya operasional Mobil Tangki
    const LORI_EXPENSE_CATEGORIES = [
        'Gaji',
        'Maintenance',
        'Umum',
    ];

    protected $fillable = [
        'kapal_id',
        'date',
        'description',
        'nominal',
        'category',
        'noted',
        'created_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'date'    => 'date',
            'nominal' => 'decimal:2',
        ];
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
