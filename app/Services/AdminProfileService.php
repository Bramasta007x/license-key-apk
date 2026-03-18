<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;

class AdminProfileService
{
    public function updatePassword($admin, array $data)
    {
        $admin->password_hash = Hash::make($data['password']);
        $admin->save();

        return $admin;
    }
}
