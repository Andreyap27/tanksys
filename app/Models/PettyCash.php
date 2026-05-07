<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PettyCash extends Model
{
    use HasUuids;

    protected $fillable = ['name'];

    public function transactions()
    {
        return $this->hasMany(PettyCashTransaction::class);
    }
}
