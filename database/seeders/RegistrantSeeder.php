<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Registrant;
use Illuminate\Support\Str;

class RegistrantSeeder extends Seeder
{
    public function run(): void
    {
        $registrants = [
            [
                "unique_code" => "JMF-2025-0001",
                "name" => "Michael Tan",
                "email" => "michael.tan@example.com",
                "phone" => "081234567890",
                "gender" => "M",
                "birthdate" => "1996-09-20",
                "document" => "data:application/pdf;base64,JVBERi0xLjQKJ...", 
                "total_cost" => 450000,
                "total_tickets" => 1,
                "status" => "paid",
            ],
            [
                "unique_code" => "JMF-2025-0002",
                "name" => "Dewi Lestari",
                "email" => "dewi.lestari@example.com",
                "phone" => "081298765432",
                "gender" => "F",
                "birthdate" => "1998-05-15",
                "document" => "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQ...",
                "total_cost" => 700000,
                "total_tickets" => 2,
                "status" => "pending",
            ],
        ];

        foreach ($registrants as $r) {
            Registrant::updateOrCreate(
                ["email" => $r["email"]],
                array_merge($r, ["id" => Str::uuid()]),
            );
        }
    }
}
