<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasUuids;

    protected $fillable = [
        'customer_id',
        'name',
        'address',
        'pic_name',
        'contact',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function loris()
    {
        return $this->hasMany(Lori::class);
    }
}
