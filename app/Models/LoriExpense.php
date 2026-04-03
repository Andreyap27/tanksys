<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoriExpense extends Model
{
    use HasUuids, SoftDeletes;

    const CATEGORIES = ['BBM', 'Gaji', 'Maintenance', 'Umum'];

    protected $fillable = [
        'mobil_id',
        'date',
        'description',
        'category',
        'nominal',
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
            $model->deleted_by = auth()->id();
        });
    }
}
