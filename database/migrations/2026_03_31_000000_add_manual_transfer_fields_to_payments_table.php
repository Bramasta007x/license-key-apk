<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_proof_url', 500)->nullable()->after('transaction_id');
            $table->string('payment_recipient_account', 100)->nullable()->after('payment_proof_url');
            $table->text('admin_notes')->nullable()->after('payment_recipient_account');
            $table->timestamp('approved_at')->nullable()->after('admin_notes');
            $table->foreignUuid('approved_by')->nullable()->after('approved_at')->constrained('admins')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'payment_proof_url',
                'payment_recipient_account',
                'admin_notes',
                'approved_at',
                'approved_by',
            ]);
        });
    }
};
