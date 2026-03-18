<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create("orders", function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table
                ->foreignUuid("registrant_id")
                ->constrained("registrants")
                ->cascadeOnDelete();
            $table->string("order_number")->unique();
            $table->decimal("amount", 12, 2)->default(0);
            $table->string("currency", 10)->default("IDR");
            $table->string("payment_method")->default("midtrans_snap"); // [v2]
            $table
                ->enum("payment_status", [
                    "pending",
                    "paid",
                    "failed",
                    "expired",
                    "cancelled",
                ])
                ->default("pending"); // [v2]
            $table->string("midtrans_transaction_id")->nullable();
            $table->string("midtrans_va_number")->nullable();
            $table->string("payment_channel")->nullable();
            $table->timestamp("payment_time")->nullable();
            $table->timestamp("expires_at")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("orders");
    }
};
