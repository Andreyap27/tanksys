<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PettyCashTransaction extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'petty_cash_id', 'type', 'date', 'description', 'amount', 'note', 'created_by', 'deleted_by',
    ];

    protected $casts = ['date' => 'date'];

    public function pettyCash()
    {
        return $this->belongsTo(PettyCash::class);
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
        static::deleting(function ($t) {
            if (!$t->isForceDeleting()) {
                $t->deleted_by = auth()->id();
                $t->saveQuietly();
            }
        });
    }
}
