<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Kapal extends Model
{
    use HasUuids;

    protected $fillable = ['code', 'name'];

    public static function generateNextCode(): string
    {
        $latest = static::orderByRaw('LENGTH(code) DESC, code DESC')->value('code');
        if (!$latest) return 'K001';
        $num = (int) substr($latest, 1);
        return 'K' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }
}
