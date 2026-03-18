<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendee;
use App\Models\Registrant;
use App\Models\Ticket;
use Illuminate\Support\Str;

class AttendeeSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan tiket ada
        $gold = Ticket::where("code", "GOLD")->first();
        $silver = Ticket::where("code", "SILVER")->first();

        if (!$gold || !$silver) {
            $this->command->error(
                "❌ Tickets GOLD or SILVER not found. Run TicketSeeder first!",
            );
            return;
        }

        // Pastikan registrant ada
        $r1 = Registrant::where("email", "michael.tan@example.com")->first();
        $r2 = Registrant::where("email", "dewi.lestari@example.com")->first();

        if (!$r1) {
            $this->command->warn(
                "⚠️ Registrant michael.tan@example.com not found. Creating fallback...",
            );
            $r1 = Registrant::create([
                "id" => Str::uuid(),
                "unique_code" => "JMF-2025-FIX01",
                "name" => "Michael Tan",
                "email" => "michael.tan@example.com",
                "phone" => "081234567890",
                "gender" => "M",
                "birthdate" => "1996-09-20",
                "document" => "data:application/pdf;base64,JVBERi0xLjQKJ...",
                "total_cost" => 450000,
                "total_tickets" => 1,
                "status" => "paid",
            ]);
        }

        if (!$r2) {
            $this->command->warn(
                "⚠️ Registrant dewi.lestari@example.com not found. Creating fallback...",
            );
            $r2 = Registrant::create([
                "id" => Str::uuid(),
                "unique_code" => "JMF-2025-FIX02",
                "name" => "Dewi Lestari",
                "email" => "dewi.lestari@example.com",
                "phone" => "081298765432",
                "gender" => "F",
                "birthdate" => "1998-05-15",
                "document" => "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQ...",
                "total_cost" => 700000,
                "total_tickets" => 2,
                "status" => "pending",
            ]);
        }

        // Data attendees
        $attendees = [
            [
                "registrant_id" => $r1->id,
                "ticket_id" => $gold->id,
                "name" => "Michael Tan",
                "gender" => "M",
                "birthdate" => "1996-09-20",
                "document" => "data:application/pdf;base64,JVBERi0xLjQKJ...",
            ],
            [
                "registrant_id" => $r2->id,
                "ticket_id" => $silver->id,
                "name" => "Dewi Lestari",
                "gender" => "F",
                "birthdate" => "1998-05-15",
                "document" => "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQ...",
            ],
        ];

        foreach ($attendees as $a) {
            Attendee::updateOrCreate(
                [
                    "registrant_id" => $a["registrant_id"],
                    "name" => $a["name"],
                ],
                array_merge($a, ["id" => Str::uuid()]),
            );
        }

        $this->command->info(
            "✅ Attendees seeded successfully (" .
                count($attendees) .
                " records).",
        );
    }
}
