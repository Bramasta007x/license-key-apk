<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $order = Order::where("order_number", "JMF2025-00001")->first();

        Payment::updateOrCreate(
            ["order_id" => $order->id],
            [
                "id" => Str::uuid(),
                "amount" => 450000,
                "method" => "midtrans_snap",
                "status" => "success",
                "transaction_id" => "MID-001",
                "raw_payload" => json_encode(["dummy" => "payload"]),
                "created_at" => Carbon::now()->subDays(2),
                "updated_at" => Carbon::now()->subDays(2),
            ],
        );
    }
}
