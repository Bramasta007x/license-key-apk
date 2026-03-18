<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(array $credentials)
    {
        $admin = Admin::where("email", $credentials["email"])->first();

        if (
            !$admin ||
            !Hash::check($credentials["password"], $admin->password_hash)
        ) {
            throw ValidationException::withMessages([
                "email" => ["Email atau password salah."],
            ]);
        }

        // Generate Sanctum token
        $token = $admin->createToken("admin_token")->plainTextToken;

        return [
            "token" => $token,
            "token_type" => "bearer",
            "expires_in" => 3600,
        ];
    }

    public function register(array $data)
    {
        $admin = Admin::create([
            "name" => $data["name"],
            "email" => $data["email"],
            "password_hash" => Hash::make($data["password"]),
            "role" => $data["role"] ?? "admin",
        ]);

        $token = $admin->createToken("admin_token")->plainTextToken;

        return [
            "token" => $token,
            "token_type" => "bearer",
            "expires_in" => 3600,
        ];
    }
}
