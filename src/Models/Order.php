<?php

declare(strict_types=1);

namespace AstralPayment\Models;

/**
 * Modèle de commande lié à un PaymentIntent Stripe.
 */
final class Order
{
    public int $id = 0;
    public int $user_id = 0;
    public int $total_cents = 0;
    public string $currency = 'eur';
    public string $status = 'pending'; // pending | paid | failed | refunded
    public ?string $stripe_intent_id = null;
    public string $created_at = '';

    public function formattedTotal(): string
    {
        return number_format($this->total_cents / 100, 2, ',', ' ') . ' €';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'user_id'          => $this->user_id,
            'total_cents'      => $this->total_cents,
            'currency'         => $this->currency,
            'status'           => $this->status,
            'stripe_intent_id' => $this->stripe_intent_id,
            'created_at'       => $this->created_at,
        ];
    }
}
