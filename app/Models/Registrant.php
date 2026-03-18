<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;

class Registrant extends Model
{
    use HasFactory, HasUuids;

    protected $table = "registrants";
    protected $keyType = "string";
    public $incrementing = false;

    protected $fillable = [
        "unique_code",
        "ticket_id",
        "name",
        "email",
        "phone",
        "gender",
        "birthdate",
        "document",
        "total_cost",
        "total_tickets",
        "status",
    ];

    /**
     * Get the registrant that owns the attendee.
     *
     * @return HasMany<\App\Models\Attendee, self>
     */
    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class, "registrant_id");
    }

    /**
     * Get the registrant that owns the attendee.
     *
     * @return HasOne<\App\Models\Order, self>
     */
    public function order(): HasOne
    {
        return $this->hasOne(Order::class, "registrant_id");
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, "ticket_id");
    }

    public function scopeFilterTickets($query, $tickets = [])
    {
        if (!empty($tickets)) {
            Log::info('Applying scopeFilterTickets', ['tickets' => $tickets]);
            
            $query->where(function ($q) use ($tickets) {
                $q->whereHas('ticket', function ($sub) use ($tickets) {
                    $sub->whereIn('code', $tickets);
                })
                    ->orWhereHas('attendees.ticket', function ($sub) use ($tickets) {
                        $sub->whereIn('code', $tickets);
                    });
            });
        }
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
