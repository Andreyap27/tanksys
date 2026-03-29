<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LoriExpense extends Model
{
    use HasUuids;

    const CATEGORIES = ['BBM', 'Gaji', 'Maintenance', 'Umum'];

    protected $fillable = [
        'date',
        'description',
        'category',
        'nominal',
        'noted',
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
}
