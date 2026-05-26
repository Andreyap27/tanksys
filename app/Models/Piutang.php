<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Piutang extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'date', 'nama', 'description', 'nominal', 'note',
        'status', 'approved_by', 'approved_at', 'created_by', 'deleted_by',
    ];

    protected $casts = [
        'date'        => 'datetime',
        'nominal'     => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function ($piutang) {
            if (!$piutang->isForceDeleting()) {
                $piutang->deleted_by = auth()->id();
                $piutang->saveQuietly();
            }
        });
    }

    public function creator()   { return $this->belongsTo(User::class, 'created_by'); }
    public function approver()  { return $this->belongsTo(User::class, 'approved_by'); }
    public function deleter()   { return $this->belongsTo(User::class, 'deleted_by'); }
}
