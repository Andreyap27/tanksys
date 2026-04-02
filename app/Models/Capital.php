<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Capital extends Model
{
    use HasUuids;

    const NAMES = ['PT ALDIVE', 'RUDI HARTONO'];

    protected $fillable = [
        'kapal_id',
        'date',
        'name',
        'nominal',
        'note',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
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

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
