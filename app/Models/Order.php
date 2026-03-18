<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, HasUuids;

    protected $table = "orders";
    protected $keyType = "string";
    public $incrementing = false;

    protected $fillable = [
        "registrant_id",
        "order_number",
        "amount",
        "currency",
        "payment_method",
        "payment_status",
        "midtrans_transaction_id",
        "midtrans_va_number",
        "payment_channel",
        "payment_time",
        "expires_at",
    ];

    protected $dates = ["payment_time", "expires_at"];

    const STATUS_PENDING = "pending";
    const STATUS_PAID = "paid";
    const STATUS_FAILED = "failed";
    const STATUS_EXPIRED = "expired";
    const STATUS_CANCELLED = "cancelled";

    /* ===============================
       RELATIONS
    =============================== */

    /**
     * Get the registrant that owns the attendee.
     *
     * @return BelongsTo<\App\Models\Registrant, self>
     */
    public function registrant(): BelongsTo
    {
        return $this->belongsTo(Registrant::class, "registrant_id");
    }

    /**
     * Get the registrant that owns the attendee.
     *
     * @return BelongsTo<\App\Models\Payment, self>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, "order_id");
    }
}
