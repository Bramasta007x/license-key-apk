<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $tickets = [
            [
                "id" => Str::uuid(),
                "code" => "GOLD",
                "title" => "Gold",
                "description" => "VIP Access with Backstage Meet & Greet",
                "price" => 450000,
                "total" => 500,
                "remaining" => 500,
                "is_presale" => false,
                "order_priority" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            [
                "id" => Str::uuid(),
                "code" => "SILVER",
                "title" => "Silver",
                "description" => "Standard Access with Preferred Zone",
                "price" => 350000,
                "total" => 1500,
                "remaining" => 1500,
                "is_presale" => false,
                "order_priority" => 2,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            [
                "id" => Str::uuid(),
                "code" => "FESTIVAL",
                "title" => "Festival",
                "description" => "General Admission - Standing Area",
                "price" => 250000,
                "total" => 4000,
                "remaining" => 4000,
                "is_presale" => false,
                "order_priority" => 3,
                "created_at" => $now,
                "updated_at" => $now,
            ],
        ];

        DB::table("tickets")->insert($tickets);

        $this->command->info(
            "✅ Tickets seeded: 3 records (Gold, Silver, Festival).",
        );
    }
}
