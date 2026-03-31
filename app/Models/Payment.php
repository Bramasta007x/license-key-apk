<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'payments';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'amount',
        'method',
        'status',
        'transaction_id',
        'raw_payload',
        'payment_proof_url',
        'payment_recipient_account',
        'admin_notes',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];

    /**
     * Get the registrant that owns the attendee.
     *
     * @return BelongsTo<\App\Models\Order, self>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
