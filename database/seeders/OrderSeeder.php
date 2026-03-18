<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Registrant;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $registrants = Registrant::all();

        $orders = [
            [
                "registrant_id" => $registrants[0]->id,
                "order_number" => "JMF2025-00001",
                "amount" => 450000,
                "currency" => "IDR",
                "payment_method" => "midtrans_snap",
                "payment_status" => "paid",
                "midtrans_transaction_id" => "MID-001",
                "payment_channel" => "bca_va",
                "payment_time" => Carbon::now()->subDays(2),
                "expires_at" => Carbon::now()->subDay(),
            ],
            [
                "registrant_id" => $registrants[1]->id,
                "order_number" => "JMF2025-00002",
                "amount" => 700000,
                "currency" => "IDR",
                "payment_method" => "midtrans_snap",
                "payment_status" => "pending",
                "midtrans_transaction_id" => "MID-002",
                "payment_channel" => "gopay",
                "expires_at" => Carbon::now()->addDay(),
            ],
        ];

        foreach ($orders as $o) {
            Order::updateOrCreate(
                ["order_number" => $o["order_number"]],
                array_merge($o, ["id" => Str::uuid()]),
            );
        }
    }
}
