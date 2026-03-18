<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ["email" => "jayapuramusicfest@gmail.com"],
            [
                "id" => Str::uuid(),
                "name" => "Super Admin",
                "password_hash" => Hash::make("admin123"),
                "role" => "superadmin",
            ],
        );
    }
}
