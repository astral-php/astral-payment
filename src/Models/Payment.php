<?php

declare(strict_types=1);

namespace AstralPayment\Models;

/**
 * Modèle de transaction Stripe.
 */
final class Payment
{
    public int $id = 0;
    public int $order_id = 0;
    public string $stripe_intent_id = '';
    public int $amount_cents = 0;
    public string $currency = 'eur';
    public string $status = 'pending'; // pending | succeeded | failed | refunded
    public string $created_at = '';

    public function formattedAmount(): string
    {
        return number_format($this->amount_cents / 100, 2, ',', ' ') . ' €';
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'order_id'         => $this->order_id,
            'stripe_intent_id' => $this->stripe_intent_id,
            'amount_cents'     => $this->amount_cents,
            'currency'         => $this->currency,
            'status'           => $this->status,
            'created_at'       => $this->created_at,
        ];
    }
}
