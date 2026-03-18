<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasUuids, HasApiTokens, HasFactory;

    protected $keyType = "string";
    public $incrementing = false;

    protected $table = "admins";

    protected $fillable = ["name", "email", "password_hash", "role"];

    protected $hidden = ["password_hash", "remember_token"];

    // Override default password field untuk Auth
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
