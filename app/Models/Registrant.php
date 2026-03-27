<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Registrant extends Model
{
    use HasFactory, HasUuids;

    protected $table = "registrants";
    protected $keyType = "string";
    public $incrementing = false;

    protected $fillable = [
        "serial_number", 
        "name",
        "email",
        "phone",
        "total_cost",
        "status", // pending / active
        "machine_id",
    ];

    /**
     * Get the order associated with the registrant.
     *
     * @return HasOne<\App\Models\Order, self>
     */
    public function order(): HasOne
    {
        return $this->hasOne(Order::class, "registrant_id");
    }

    public function scopeFilterPaymentStatus($query, $status = [])
    {
        if (!empty($status)) {
            $query->whereHas(
                "order",
                fn($q) => $q->whereIn("payment_status", $status),
            );
        }
    }
}