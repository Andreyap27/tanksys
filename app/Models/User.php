<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'employee_id',
        'name',
        'role',
        'username',
        'password',
        'reset_password',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password'       => 'hashed',
            'reset_password' => 'boolean',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role'        => $this->role,
            'employee_id' => $this->employee_id,
            'name'        => $this->name,
        ];
    }

    public function isSPV(): bool
    {
        return $this->role === 'SPV';
    }

    public function canApprove(): bool
    {
        return in_array($this->role, ['SPV', 'Super Admin']);
    }
}
