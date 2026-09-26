<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * `user_id` is set through the relationship, never from input.
     *
     * @var list<string>
     */
    protected $fillable = [
        'status',
        'subtotal_cents',
        'currency',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'paid_at',
    ];

    protected $attributes = [
        'status' => 'pending',
        'currency' => 'usd',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal_cents' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isPaid(): bool
    {
        return $this->status === OrderStatus::Paid;
    }

    public function isPending(): bool
    {
        return $this->status === OrderStatus::Pending;
    }

    /**
     * Human-friendly order number, e.g. "BP-000042".
     *
     * @return Attribute<string, never>
     */
    protected function reference(): Attribute
    {
        return Attribute::get(fn () => 'BP-'.str_pad((string) $this->getKey(), 6, '0', STR_PAD_LEFT));
    }
}
