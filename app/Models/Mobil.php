<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Mobil extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'plat_nomer'];
}
