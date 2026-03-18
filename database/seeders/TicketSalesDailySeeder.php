<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;
use App\Models\TicketSalesDaily;
use Carbon\Carbon;

class TicketSalesDailySeeder extends Seeder
{
    public function run(): void
    {
        $tickets = Ticket::all();

        foreach ($tickets as $t) {
            for ($i = 0; $i < 5; $i++) {
                TicketSalesDaily::updateOrCreate(
                    [
                        "ticket_id" => $t->id,
                        "date" => Carbon::now()->subDays($i)->toDateString(),
                    ],
                    [
                        "sold" => rand(5, 20),
                    ],
                );
            }
        }
    }
}
