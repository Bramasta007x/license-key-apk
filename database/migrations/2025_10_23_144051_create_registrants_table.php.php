<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create("registrants", function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string("serial_number")->unique();
            $table->string("machine_id")->nullable();
            $table->string("name");
            $table->string("email")->unique();
            $table->string("phone")->unique();
            $table->decimal("total_cost", 12, 2)->default(0);
            $table
                ->enum("status", ["pending", "paid", "failed", "cancelled"])
                ->default("pending");
            $table->timestamps();
        });
    }

    /**
     * Rollback migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists("registrants");
    }
};
